<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class AttendanceService
{
    /**
     * Get all attendance records with stats
     */
    public function getAllAttendances(array $filters = [], bool $paginate = true): array
    {
        try {
            $query = Attendance::with(['employee.department', 'employee.jobTitle', 'employee.employeeType', 'employee.officeLocation']);

            // Date filtering: range takes priority over single date
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->whereDate('date', '>=', $filters['date_from'])
                      ->whereDate('date', '<=', $filters['date_to']);
            } elseif (!empty($filters['date'])) {
                $query->whereDate('date', $filters['date']);
            } else {
                $query->whereDate('date', now()->toDateString());
            }

            if (!empty($filters['department_id'])) {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $filters['department_id']));
            }

            if (!empty($filters['employee_id'])) {
                $query->where('employee_id', $filters['employee_id']);
            }

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $aggregate = (clone $query)->toBase()->select(
                DB::raw('SUM(CASE WHEN status = ' . Attendance::STATUS_PRESENT . ' THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count'),
                DB::raw('SUM(CASE WHEN is_early_out = 1 THEN 1 ELSE 0 END) as early_out_count'),
                DB::raw('SUM(CASE WHEN status = ' . Attendance::STATUS_ABSENT . ' THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN status = ' . Attendance::STATUS_WEEKEND . ' THEN 1 ELSE 0 END) as weekend_count'),
                DB::raw('SUM(CASE WHEN status = ' . Attendance::STATUS_LEAVE . ' THEN 1 ELSE 0 END) as leave_count')
            )->first();

            $stats = [
                'total_employees' => Employee::count(),
                'present'         => $aggregate->present_count ?? 0,
                'late'            => $aggregate->late_count ?? 0,
                'early_out'       => $aggregate->early_out_count ?? 0,
                'absent'          => $aggregate->absent_count ?? 0,
                'weekend'         => $aggregate->weekend_count ?? 0,
                'leave'           => $aggregate->leave_count ?? 0,
            ];

            $sortBy    = $filters['sort_by']    ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $list = $paginate ? $query->paginate($filters['per_page'] ?? 25) : $query->get();

            return ['stats' => $stats, 'list' => $list];
        } catch (\Exception $e) {
            Log::error('Error fetching attendance: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch attendance records');
        }
    }

    /**
     * Get Attendance by ID
     */
    public function getAttendanceById(int $id): Attendance
    {
        $attendance = Attendance::with(['employee.department', 'employee.jobTitle'])->find($id);

        if (!$attendance) {
            throw ApiException::notFound('Attendance record');
        }

        return $attendance;
    }

    /**
     * Create a single attendance record.
     * If a record already exists for the same employee + date (even in trash), 
     * it is force-deleted first so a clean new record is inserted.
     */
    public function createAttendance(array $data): Attendance
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($data['employee_id']);

            // Remove any existing record (including soft-deleted) for same employee+date
            Attendance::withTrashed()
                ->where('employee_id', $data['employee_id'])
                ->whereDate('date', $data['date'])
                ->forceDelete();

            $data       = $this->calculateAttendanceMetrics($data, $employee);
            $attendance = Attendance::create($data);

            $message = sprintf(
                'Employee %s %s marked as %s',
                $employee->first_name,
                $employee->last_name,
                $attendance->status_text
            );

            LogHelper::created('attendance', $attendance->id, $attendance->company_id, $message);
            DB::commit();
            Log::info('Attendance created (replaced existing)', ['id' => $attendance->id]);

            return $attendance->load('employee');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create attendance');
        }
    }

    /**
     * Bulk create attendance records.
     * For each record, any existing entry (including soft-deleted) for the same
     * employee + date is force-deleted before the new one is inserted.
     * Returns ['saved' => int, 'failed' => int, 'errors' => array]
     */
    public function bulkCreateAttendance(array $records): array
    {
        $saved  = 0;
        $failed = 0;
        $errors = [];

        $employeeIds = array_unique(array_column($records, 'employee_id'));
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');

        // Optional: Bulk force delete if all records share the same date (common case)
        $dates = array_unique(array_column($records, 'date'));
        if (count($dates) === 1) {
            Attendance::withTrashed()
                ->whereIn('employee_id', $employeeIds)
                ->whereDate('date', $dates[0])
                ->forceDelete();
        }

        foreach ($records as $index => $data) {
            DB::beginTransaction();
            try {
                $employee = $employees->get($data['employee_id']);

                if (!$employee) {
                    $failed++;
                    $errors[$index] = "Employee #{$data['employee_id']} not found";
                    DB::rollBack();
                    continue;
                }

                if (count($dates) > 1) {
                    // Remove any existing record (including soft-deleted) for same employee+date
                    Attendance::withTrashed()
                        ->where('employee_id', $data['employee_id'])
                        ->whereDate('date', $data['date'])
                        ->forceDelete();
                }

                $data       = $this->calculateAttendanceMetrics($data, $employee);
                $attendance = Attendance::create($data);

                $message = sprintf(
                    'Employee %s %s marked as %s (bulk)',
                    $employee->first_name,
                    $employee->last_name,
                    $attendance->status_text
                );

                LogHelper::created('attendance', $attendance->id, $attendance->company_id, $message);

                DB::commit();
                $saved++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[$index] = $e->getMessage();
                Log::warning("Bulk attendance failed for index {$index}: " . $e->getMessage());
            }
        }

        Log::info("Bulk attendance complete: {$saved} saved, {$failed} failed");

        return compact('saved', 'failed', 'errors');
    }

    /**
     * Update attendance
     */
    public function updateAttendance(int $id, array $data): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);
            $data       = $this->calculateAttendanceMetrics($data, $attendance->employee);
            $attendance->update($data);

            $message = sprintf(
                'Employee %s %s marked as %s',
                $attendance->employee->first_name,
                $attendance->employee->last_name,
                $attendance->status_text
            );

            DB::commit();
            LogHelper::updated('attendance', $id, $attendance->company_id, $message);

            return $attendance->fresh('employee');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update attendance');
        }
    }

    /**
     * Calculate Late Time, Working Hours, and Overtime
     */
    private function calculateAttendanceMetrics(array $data, $employee): array
    {
        $in  = !empty($data['in_time'])  ? Carbon::parse($data['in_time'])  : null;
        $out = !empty($data['out_time']) ? Carbon::parse($data['out_time']) : null;

        if ($in && !empty($employee->in_time)) {
            $officeIn = Carbon::parse($employee->in_time);
            $grace    = isset($data['grace_time']) ? (int) $data['grace_time'] : 15;

            if ($in->greaterThan($officeIn->copy()->addMinutes($grace))) {
                $data['is_late']   = true;
                $data['late_time'] = $in->diff($officeIn)->format('%H:%I');
            } else {
                $data['is_late']   = false;
                $data['late_time'] = null;
            }
        }

        if ($in && $out) {
            $data['working_hours'] = $out->diff($in)->format('%H:%I');

            if (!empty($employee->out_time)) {
                $officeOut = Carbon::parse($employee->out_time);

                $data['over_time']    = $out->greaterThan($officeOut)
                    ? $out->diff($officeOut)->format('%H:%I')
                    : null;

                $data['is_early_out'] = $out->lessThan($officeOut);
            } else {
                $totalMins = $out->diffInMinutes($in);
                $data['over_time'] = $totalMins > 480
                    ? $out->diff($in->copy()->addMinutes(480))->format('%H:%I')
                    : null;
            }
        }

        return $data;
    }

    /**
     * Soft delete
     */
    public function deleteAttendance(int $id): bool
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);
            $attendance->delete();
            DB::commit();
            Log::info('Attendance moved to trash', ['id' => $id]);
            LogHelper::deleted('attendance', $id, $attendance->company_id);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete attendance');
        }
    }

    /**
     * Restore
     */
    public function restoreAttendance(int $id): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);
            if (!$attendance) throw ApiException::notFound('Attendance');
            $attendance->restore();
            DB::commit();
            Log::info('Attendance restored', ['id' => $id]);
            LogHelper::custom('restored', 'attendance', $id, $attendance->company_id);
            return $attendance;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Attendance restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore attendance');
        }
    }

    /**
     * Force delete
     */
    public function forceDeleteAttendance(int $id): bool
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);
            if (!$attendance) throw ApiException::notFound('Attendance record');

            $companyId = $attendance->company_id;
            $attendance->forceDelete();
            DB::commit();
            Log::info('Attendance permanently deleted', ['id' => $id]);
            LogHelper::custom('force_deleted', 'attendance', $id, $companyId);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete attendance');
        }
    }

    /**
     * Change status
     */
    public function changeAttendanceStatus(int $id, int $status): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);
            $attendance->update(['status' => $status]);
            DB::commit();
            LogHelper::custom('status_changed', 'attendance', $id, $attendance->company_id, $attendance->status_text);
            return $attendance;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change attendance status');
        }
    }
}

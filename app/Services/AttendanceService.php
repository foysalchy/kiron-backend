<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class AttendanceService
{
    /**
     * Get all attendance records with stats (Matches Image UI)
     */
    public function getAllAttendances(array $filters = [], bool $paginate = true): array
    {
        try {
            $query = Attendance::with(['employee.department', 'employee.jobTitle','employee.employeeType', 'employee.officeLocation']);

            $date = $filters['date'] ?? now()->toDateString();
            $query->whereDate('date', $date);

            if (!empty($filters['department_id'])) {
                $query->whereHas('employee', fn($q)
                => $q->where('department_id', $filters['department_id']));
            }

            if (isset($filters['status']) && $filters['status'] !== "") {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $statsQuery = clone $query;
            $allRecords = $statsQuery->get();

            $stats = [
                'total_employees' => Employee::count(),
                'present'         => $allRecords->where('status', Attendance::STATUS_PRESENT)->count(),
                'late'            => $allRecords->where('is_late', true)->count(),
                'early_out'       => $allRecords->where('is_early_out', true)->count(),
                'absent'          => $allRecords->where('status', Attendance::STATUS_ABSENT)->count(),
                'weekend'         => $allRecords->where('status', Attendance::STATUS_WEEKEND)->count(),
            ];

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $list = $paginate ? $query->paginate($filters['per_page'] ?? 25) : $query->get();

            return [
                'stats' => $stats,
                'list'  => $list
            ];

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
     * Create attendance
     */
    public function createAttendance(array $data): Attendance
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($data['employee_id']);

            // Calculate metrics before saving
            $data = $this->calculateAttendanceMetrics($data, $employee);

            $attendance = Attendance::create($data);

            DB::commit();
            Log::info('Attendance created', ['id' => $attendance->id]);
            LogHelper::created('attendance', $attendance->id, $attendance->company_id);

            return $attendance->load('employee');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create attendance');
        }
    }

    /**
     * Update attendance
     */
    public function updateAttendance(int $id, array $data): Attendance
    {
        DB::beginTransaction();

        try {
            $attendance = $this->getAttendanceById($id);
            $data = $this->calculateAttendanceMetrics($data, $attendance->employee);
            $attendance->update($data);
            DB::commit();

            LogHelper::updated('attendance', $id, $attendance->company_id);
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
        $in = !empty($data['in_time']) ? Carbon::parse($data['in_time']) : null;
        $out = !empty($data['out_time']) ? Carbon::parse($data['out_time']) : null;

        if ($in && !empty($employee->in_time)) {
            $officeIn = Carbon::parse($employee->in_time);
            $grace = isset($data['grace_time']) ? (int) $data['grace_time'] : 15;

            if ($in->greaterThan($officeIn->copy()->addMinutes($grace))) {
                $data['is_late'] = true;
                $data['late_time'] = $in->diff($officeIn)->format('%H:%I');
            } else {
                $data['is_late'] = false;
                $data['late_time'] = null;
            }
        }

        if ($in && $out) {
            $data['working_hours'] = $out->diff($in)->format('%H:%I');

            if (!empty($employee->out_time)) {
                $officeOut = Carbon::parse($employee->out_time);

                if ($out->greaterThan($officeOut)) {
                    $data['over_time'] = $out->diff($officeOut)->format('%H:%I');
                } else {
                    $data['over_time'] = null;
                }

                // Early Out check
                $data['is_early_out'] = $out->lessThan($officeOut);
            } else {
                $totalMins = $out->diffInMinutes($in);
                if ($totalMins > 480) {
                    $data['over_time'] = $out->diff($in->copy()->addMinutes(480))->format('%H:%I');
                } else {
                    $data['over_time'] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Delete Attendance soft delete
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
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete attendance');
        }
    }

    /**
     * Restore Attendance
     */
    public function restoreAttendance(int $id): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);
            if (!$attendance) throw ApiException::notFound('Attendance');
            $attendance->restore();

            DB::commit();
            Log::info('Attendance restored from trash', ['id' => $id]);
            LogHelper::custom('restored', 'attendance', $id, $attendance->company_id);
            return $attendance;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Attendance restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore attendance');
        }
    }

    /**
     * Permanently Delete
     */
   public function forceDeleteAttendance(int $id): bool
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);

            if (!$attendance) {
                throw ApiException::notFound('Attendance record');
            }

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
     * Change Status to a specific value
     */
    public function changeAttendanceStatus(int $id, int $status): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);

            $attendance->update(['status' => $status]);

            DB::commit();
            LogHelper::custom('status_changed', 'attendance', $id, $attendance->company_id);

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

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

            $date = $filters['date'] ?? now()->format('Y-m-d');
            $query->whereDate('date', $date);

            if (!empty($filters['department_id'])) {
                $query->whereHas('employee', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            }

            if (isset($filters['status']) && $filters['status'] !== "") {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $statsQuery = clone $query;
            $allAttendance = $statsQuery->get();

            $stats = [
                'total_employees' => Employee::count(),
                'present'         => $allAttendance->where('status', 'Present')->count(),
                'late'            => $allAttendance->where('is_late', true)->count(),
                'early_out'       => $allAttendance->where('is_early_out', true)->count(),
                'absent'          => $allAttendance->where('status', 'Absent')->count(),
                'weekend'         => $allAttendance->where('status', 'Weekend')->count(),
            ];

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $list = $paginate
                ? $query->paginate($filters['per_page'] ?? 25)
                : $query->get();

            return [
                'stats' => $stats,
                'list'  => $list
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching attendance: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch attendance sheet');
        }
    }
    /**
     * Get Attendance by ID
     */
    public function getAttendanceById(int $id): Attendance
    {
        $attendance = Attendance::with(['employee.department'])->find($id);

        if (!$attendance) {
            throw ApiException::notFound('Attendance record');
        }
        return $attendance;
    }
    /**
     * Create Attendance
     */
    public function createAttendance(array $data): Attendance
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($data['employee_id']);
            // Logic for Late and Working Hours
            $processedData = $this->processAttendanceData($data, $employee);

            $attendance = Attendance::create($processedData);
            DB::commit();
            Log::info('Attendance created', ['id' => $attendance->id]);
            LogHelper::created('attendance', $attendance->id, $attendance->company_id);

            return $attendance->load(['employee']);
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
            $employee = $attendance->employee;

            $processedData = $this->processAttendanceData($data, $employee);
            $attendance->update($processedData);

            DB::commit();

            Log::info('Attendance updated', ['id' => $id]);
            LogHelper::updated('attendance', $id, $attendance->company_id);

            return $attendance->fresh(['employee']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update attendance');
        }
    }
    /**
     * Delete attendance (soft delete)
     */
    public function deleteAttendance(int $id): bool
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);
            $attendance->delete();

            LogHelper::deleted('attendance', $id, $attendance->company_id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete attendance');
        }
    }
    /**
     * Helper to calculate Late Time, Overtime, and Working Hours
     */
    public function processAttendanceData(array $data, Employee $employee):array
    {
        if (!empty($data['in_time'])) {
            $officeIn = Carbon::parse($employee->in_time);
            $actualIn = Carbon::parse($data['in_time']);
            $grace = $data['grace_time'] ?? 0;

            // Late Calculation
            if ($actualIn->greaterThan($officeIn->addMinutes($grace))) {
                $data['is_late'] = true;
                $data['late_time'] = $actualIn->diff($officeIn)->format('%H:%I');
            } else {
                $data['is_late'] = false;
                $data['late_time'] = null;
            }
        }

        // Working Hours & Overtime
        if (!empty($data['in_time']) && !empty($data['out_time'])) {
            $in = Carbon::parse($data['in_time']);
            $out = Carbon::parse($data['out_time']);
            $data['working_hours'] = $out->diff($in)->format('%H:%I');

            // Example: Overtime if more than 8 hours
            if ($out->diffInMinutes($in) > 480) {
                $data['over_time'] = $out->diff($in->addMinutes(480))->format('%H:%I');
            }
        }

        return $data;

    }
    /**
     * Restore soft deleted attendance
     */
    public function restoreAttendance(int $id): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);

            if (!$attendance) {
                throw ApiException::notFound('Attendance');
            }

            $attendance->restore();

            DB::commit();

            Log::info('Attendance restored successfully', ['attendance_id' => $id]);
            LogHelper::restored('attendance', $attendance->id, $attendance->company_id);

            return $attendance->load(['employee']);

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore attendance');
        }
    }

    /**
     * Permanently delete an attendance
     */
    public function forceDeleteAttendance(int $id): bool
    {
        DB::beginTransaction();
        try {
            $attendance = Attendance::withTrashed()->find($id);

            if (!$attendance) {
                throw ApiException::notFound('Attendance');
            }

            $companyId = $attendance->company_id;
            $attendance->forceDelete();

            DB::commit();

            Log::info('Attendance permanently deleted', ['attendance_id' => $id]);
            LogHelper::forceDeleted('attendance', $id, $companyId);

            return true;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permanent attendance deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete attendance');
        }
    }
    /**
     * Toggle attendance status
     */
    public function toggleStatus(int $id): Attendance
    {
        DB::beginTransaction();
        try {
            $attendance = $this->getAttendanceById($id);

            $newStatus = $attendance->status === 'Present' ? 'Absent' : 'Present';

            $attendance->update(['status' => $newStatus]);

            LogHelper::statusChanged('attendance', $attendance->id, $attendance->company_id);

            DB::commit();
            Log::info('Attendance status toggled', ['attendance_id' => $id, 'new_status' => $newStatus]);

            return $attendance->load(['employee']);

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle attendance status');
        }
    }
}

<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\LeaveApplication;
use App\Exceptions\ApiException;
use App\Helpers\{FileUploadHelper, LogHelper};
use App\Models\AssignLeaveType;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Log};

class LeaveApplicationService
{
    public function getAllApplications(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeaveApplication::with(['employee:id,first_name,last_name', 'leave_type:id,name', 'assign_leave']);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('reason', 'like', "%{$filters['search']}%")
                        ->orWhereHas('employee', function ($subQuery) use ($filters) {
                            $subQuery->where('first_name', 'like', "%{$filters['search']}%")
                                ->orWhere('last_name', 'like', "%{$filters['search']}%");
                        });
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching leave applications: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch leave applications');
        }
    }

    public function getApplicationById(int $id): LeaveApplication
    {
        $application = LeaveApplication::with(['employee', 'leave_type'])->find($id);
        if (!$application) throw ApiException::notFound('Leave Application');
        return $application;
    }

    /**
     * ---------------------------------------------------------
     * HELPER: Check if dates overlap with existing Pending/Approved leaves
     * ---------------------------------------------------------
     */
    private function checkDateOverlap(int $employeeId, string $fromDate, string $toDate, ?int $excludeAppId = null): void
    {
        $overlappingLeave = LeaveApplication::where('employee_id', $employeeId)
            ->whereIn('status', [Status::Pending->value, Status::Approved->value])
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                    });
            });

        if ($excludeAppId) {
            $overlappingLeave->where('id', '!=', $excludeAppId);
        }

        if ($overlappingLeave->exists()) {
            throw ApiException::badRequest('Employee already has a pending or approved leave overlapping these dates.');
        }
    }

    /**
     * ---------------------------------------------------------
     * HELPER: Calculate Duration (Handles Half Days)
     * ---------------------------------------------------------
     */
    private function calculateDuration(string $fromDate, string $toDate, bool $isHalfDay): float
    {
        $start = Carbon::parse($fromDate)->startOfDay();
        $end = Carbon::parse($toDate)->startOfDay();

        if ($isHalfDay) {
            if (!$start->equalTo($end)) {
                throw ApiException::badRequest('Half day leaves must have the same start and end date.');
            }
            return 0.5;
        }

        return $start->diffInDays($end) + 1; // +1 includes both start and end days
    }

    /**
     * ---------------------------------------------------------
     * CREATE APPLICATION
     * ---------------------------------------------------------
     */
    public function createApplication(array $data): LeaveApplication
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($data['employee_id']);
            if (!$employee) throw ApiException::notFound('Employee');

            // 1. Fetch Leave Policy assigned to this Employee's position
            $assignLeave = AssignLeaveType::where('position_id', $employee->position_id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->first();

            if (!$assignLeave) {
                throw ApiException::badRequest('No leave balance assigned for this employee and leave type.');
            }

            // 2. Validate Overlaps
            $this->checkDateOverlap($employee->id, $data['from_date'], $data['to_date']);

            // 3. Calculate Duration
            $isHalfDay = isset($data['is_half_day']) ? filter_var($data['is_half_day'], FILTER_VALIDATE_BOOLEAN) : false;
            $duration = $this->calculateDuration($data['from_date'], $data['to_date'], $isHalfDay);

            // 4. Validate Balance against PENDING and APPROVED leaves
            $usedDays = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->whereIn('status', [Status::Pending->value, Status::Approved->value])
                ->sum('duration');

            if (($usedDays + $duration) > $assignLeave->leave_count) {
                $remaining = max(0, $assignLeave->leave_count - $usedDays);
                throw ApiException::badRequest("Insufficient balance. Remaining: {$remaining} days.");
            }

            // 5. Prepare Data
            $data['duration'] = $duration;
            $data['assign_leave_id'] = $assignLeave->id;
            $data['is_half_day'] = $isHalfDay;
            $data['status'] = Status::Pending->value; // Lock it in Pending state

            // 6. Handle Documents
            if (isset($data['documents']) && is_array($data['documents'])) {
                $uploadedDocs = [];
                foreach ($data['documents'] as $index => $doc) {
                    $customFileName = 'leave_doc_' . ($data['employee_id'] ?? 'emp') . '_' . ($index + 1) . '-' . time();
                    $uploadedDocs[] = FileUploadHelper::upload(
                        $doc,
                        'leaves/documents',
                        'r2',
                        false,
                        $customFileName
                    );
                }
                $data['documents'] = $uploadedDocs;
            }

            $application = LeaveApplication::create($data);

            LogHelper::created('leave_application', $application->id, $application->company_id, "Applied for {$duration} days of leave");

            DB::commit();
            return $application;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($uploadedDocs)) {
                foreach ($uploadedDocs as $path) FileUploadHelper::delete($path);
            }
            Log::error('Leave application creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to submit leave application');
        }
    }

    /**
     * ---------------------------------------------------------
     * UPDATE APPLICATION (Only allowed if Pending)
     * ---------------------------------------------------------
     */
    public function updateApplication(int $id, array $data): LeaveApplication
    {
        DB::beginTransaction();

        try {
            $application = LeaveApplication::with('employee')->findOrFail($id);

            // Restrict updates to Pending items
            if ($application->status != Status::Pending->value) {
                throw ApiException::badRequest('Only pending applications can be modified.');
            }

            $isHalfDay = isset($data['is_half_day']) ? filter_var($data['is_half_day'], FILTER_VALIDATE_BOOLEAN) : $application->is_half_day;
            $fromDate = $data['from_date'] ?? $application->from_date;
            $toDate = $data['to_date'] ?? $application->to_date;
            $leaveTypeId = $data['leave_type_id'] ?? $application->leave_type_id;

            // 1. Recalculate Duration
            $duration = $this->calculateDuration($fromDate, $toDate, $isHalfDay);
            $data['duration'] = $duration;
            $data['is_half_day'] = $isHalfDay;

            // 2. Fetch Policy
            $assignLeave = AssignLeaveType::where('position_id', $application->employee->position_id)
                ->where('leave_type_id', $leaveTypeId)
                ->first();

            if (!$assignLeave) {
                throw ApiException::badRequest('No leave balance assigned for this leave type.');
            }
            $data['assign_leave_id'] = $assignLeave->id;

            // 3. Overlap Check (Excluding current app)
            $this->checkDateOverlap($application->employee_id, $fromDate, $toDate, $application->id);

            // 4. Re-evaluate Balance (Excluding current app)
            $usedDaysExcludingSelf = LeaveApplication::where('employee_id', $application->employee_id)
                ->where('leave_type_id', $leaveTypeId)
                ->whereIn('status', [Status::Pending->value, Status::Approved->value])
                ->where('id', '!=', $application->id)
                ->sum('duration');

            if (($usedDaysExcludingSelf + $duration) > $assignLeave->leave_count) {
                $remaining = max(0, $assignLeave->leave_count - $usedDaysExcludingSelf);
                throw ApiException::badRequest("Insufficient balance for update. Remaining: {$remaining} days.");
            }

            // 5. Document Handling
            if (isset($data['documents']) && is_array($data['documents'])) {
                if (!empty($application->documents)) {
                    foreach ($application->documents as $oldPath) FileUploadHelper::delete($oldPath);
                }

                $uploadedDocs = [];
                foreach ($data['documents'] as $index => $doc) {
                    $customFileName = 'leave_doc_' . ($data['employee_id'] ?? $application->employee_id ?? 'emp') . '_' . ($index + 1) . '-' . time();
                    $uploadedDocs[] = FileUploadHelper::upload(
                        $doc,
                        'leaves/documents',
                        'r2',
                        false,
                        $customFileName
                    );
                }
                $data['documents'] = $uploadedDocs;
            }

            $application->update($data);

            LogHelper::updated('leave_application', $application->id, $application->company_id, "Leave application updated");

            DB::commit();
            return $application->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update leave application');
        }
    }

    /**
     * ---------------------------------------------------------
     * UPDATE STATUS (Approve/Reject/Cancel)
     * ---------------------------------------------------------
     */
    public function updateStatus(int $id, int $newStatusValue): LeaveApplication
    {
        DB::beginTransaction();
        try {
            $application = $this->getApplicationById($id);

            // If approving from a previously Rejected/Cancelled state, we must re-verify balance and overlaps
            if ($newStatusValue == Status::Approved->value && !in_array($application->status, [Status::Pending->value, Status::Approved->value])) {

                $policy = AssignLeaveType::find($application->assign_leave_id);
                if ($policy) {
                    // Check Balance again
                    $usedDays = LeaveApplication::where('employee_id', $application->employee_id)
                        ->where('leave_type_id', $application->leave_type_id)
                        ->whereIn('status', [Status::Pending->value, Status::Approved->value])
                        ->sum('duration');

                    if (($usedDays + $application->duration) > $policy->leave_count) {
                        throw ApiException::badRequest('Cannot approve. Insufficient leave balance remaining.');
                    }

                    // Check Overlaps again just in case another leave was approved in the meantime
                    $this->checkDateOverlap($application->employee_id, $application->from_date, $application->to_date, $application->id);
                }
            }

            $application->update(['status' => $newStatusValue]);

            LogHelper::statusChanged('leave_application', $application->id, $application->company_id, "Status changed to: {$newStatusValue}");

            DB::commit();
            return $application;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
        }
    }
    public function getLeaveBalances(array $filters = []): LengthAwarePaginator
    {
        try {
            $user = auth()->user();
            $isSuperAdmin = $user->is_super_admin == 1;
            $companyId = $user->company_id;

            $query = Employee::query()
                ->withoutGlobalScopes()
                ->whereNull('employees.deleted_at')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->join('assign_leave_types', 'employees.position_id', '=', 'assign_leave_types.position_id')
                ->join('leave_types', 'assign_leave_types.leave_type_id', '=', 'leave_types.id')
                ->select(
                    'employees.id as employee_id',
                    DB::raw("CONCAT(employees.first_name, ' ', COALESCE(employees.last_name, '')) as employee_name"),
                    'departments.name as department_name',
                    'leave_types.name as leave_type_name',
                    'leave_types.id as leave_type_id',
                    'assign_leave_types.leave_count as total'
                );

            if ($isSuperAdmin) {
                $query->whereNull('employees.company_id');
            } else {
                $query->where('employees.company_id', $companyId);
            }

            if (!empty($filters['employee_id'])) {
                $query->where('employees.id', $filters['employee_id']);
            }

            if (!empty($filters['department_id'])) {
                $query->where('employees.department_id', $filters['department_id']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('employees.first_name', 'like', "%{$search}%")
                        ->orWhere('employees.last_name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('employees.first_name', 'asc');

            $balances = $query->paginate($filters['per_page'] ?? 15);

            $activeStatuses = [Status::Pending->value, Status::Approved->value];

            foreach ($balances as $balance) {
                $leaveQuery = LeaveApplication::withoutGlobalScopes()
                    ->where('employee_id', $balance->employee_id)
                    ->where('leave_type_id', $balance->leave_type_id)
                    ->whereIn('status', $activeStatuses)
                    ->whereNull('deleted_at');

                if ($isSuperAdmin) {
                    $leaveQuery->whereNull('company_id');
                } else {
                    $leaveQuery->where('company_id', $companyId);
                }

                $used = $leaveQuery->sum('duration');

                $balance->used = (float) $used;
                $balance->remaining = (float) max(0, $balance->total - $used);
            }

            return $balances;
        } catch (\Exception $e) {
            Log::error('Error fetching leave balances: ' . $e->getMessage());
            throw ApiException::serverError('Failed to calculate leave balances.');
        }
    }
    public function deleteApplication(int $id): bool
    {
        DB::beginTransaction();
        try {
            $application = $this->getApplicationById($id);
            $application->delete();
            LogHelper::deleted('leave_application', $id, $application->company_id, "Soft deleted");
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete application');
        }
    }

    public function restoreApplication(int $id): LeaveApplication
    {
        DB::beginTransaction();
        try {
            $application = LeaveApplication::withTrashed()->find($id);
            if (!$application) throw ApiException::notFound('Leave Application');
            $application->restore();
            DB::commit();
            return $application;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore leave application');
        }
    }

    public function forceDeleteApplication(int $id): bool
    {
        DB::beginTransaction();
        try {
            $application = LeaveApplication::withTrashed()->find($id);
            if (!$application) throw ApiException::notFound('Leave Application');

            if (!empty($application->documents)) {
                foreach ($application->documents as $path) FileUploadHelper::delete($path);
            }

            $application->forceDelete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete');
        }
    }
}

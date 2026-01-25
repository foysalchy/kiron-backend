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
use Illuminate\Support\Facades\{DB, Log};

class LeaveApplicationService
{
    /**
     * Get all leave applications with optional pagination
     */
    public function getAllApplications(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeaveApplication::with(['employee:id,first_name', 'leave_type:id,name', 'assign_leave']);

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Reason or Employee Name
            if (isset($filters['search']) && $filters['search'] !== '') {
                $query->where(function ($q) use ($filters) {
                    $q->where('reason', 'like', "%{$filters['search']}%")
                      ->orWhereHas('employee', function ($subQuery) use ($filters) {
                          $subQuery->where('first_name', 'like', "%{$filters['search']}%");
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

    /**
     * Get application by ID
     */
    public function getApplicationById(int $id): LeaveApplication
    {
        $application = LeaveApplication::with(['employee', 'leave_type'])->find($id);
        if (!$application) {
            throw ApiException::notFound('Leave Application');
        }
        return $application;
    }

    /**
     * Create a new leave application with multiple documents
     */
    public function createApplication(array $data): LeaveApplication
    {
        DB::beginTransaction();

        try {
            $employee = Employee::find($data['employee_id']);
                if (!$employee) throw ApiException::notFound('Employee');

                // dd($employee);

            $assignLeave = AssignLeaveType::where('position_id', $employee->position_id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->first();
            if(!$assignLeave) {
                throw ApiException::badRequest('No leave balance assigned for this employee and leave type');
            }
            $fromDate = Carbon::parse($data['from_date']);
            $toDate = Carbon::parse($data['to_date']);
            $duration = $fromDate->diffInDays($toDate) + 1;

            $usedDays = LeaveApplication::where('employee_id', $data['employee_id'])
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('status', 3) // 3 = Approved
                ->sum('duration');

            if (($usedDays + $duration) > $assignLeave->leave_count) {
                $remaining = $assignLeave->leave_count - $usedDays;
                throw ApiException::badRequest("Insufficient balance. Remaining: {$remaining} days.");
            }

            $data['duration'] = $duration;
            $data['assign_leave_id'] = $assignLeave->id;
            

            if (isset($data['documents']) && is_array($data['documents'])) {
                $uploadedDocs = [];
                foreach ($data['documents'] as $doc) {
                    $uploadedDocs[] = FileUploadHelper::uploadImage(
                        $doc,
                        'leaves/documents',
                        'public',
                        2048
                    );
                }
                $data['documents'] = $uploadedDocs;
            }

            $application = LeaveApplication::create($data);

            LogHelper::created('leave_application', $application->id, $application->company_id, "Leave applied for {$data['duration']} days");
            
            DB::commit();
            return $application;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($uploadedDocs)) {
                foreach ($uploadedDocs as $path) {
                    FileUploadHelper::delete($path);
                }
            }

            Log::error('Leave application creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to submit leave application');
        }
    }

    /**
     * Update leave application
     */
    public function updateApplication(int $id, array $data): LeaveApplication
    {
        DB::beginTransaction();

        try {
            $application = LeaveApplication::with('employee','leaveType')->findOrFail($id);

        // 1. Re-calculate duration if dates change
        if (isset($data['from_date']) || isset($data['to_date'])) {
            $fromDate = Carbon::parse($data['from_date'] ?? $application->from_date);
            $toDate = Carbon::parse($data['to_date'] ?? $application->to_date);
            $data['duration'] = $fromDate->diffInDays($toDate) + 1;
        }

        // 2. If Leave Type changes, find the corresponding assignment policy
        if (isset($data['leave_type_id']) && $data['leave_type_id'] != $application->leave_type_id) {
            $assignLeave = AssignLeaveType::where('position_id', $application->employee->position_id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->first();

            if (!$assignLeave) {
                throw ApiException::badRequest('No leave balance assigned for this leave type.');
            }
            // Update the foreign key
            $data['assign_leave_id'] = $assignLeave->id;
        }

            // 3. Document Handling
            if (isset($data['documents']) && is_array($data['documents'])) {
                if (!empty($application->documents)) {
                    foreach ($application->documents as $oldPath) {
                        FileUploadHelper::delete($oldPath);
                    }
                }

                $uploadedDocs = [];
                foreach ($data['documents'] as $doc) {
                    $uploadedDocs[] = FileUploadHelper::uploadImage($doc, 'leaves/documents', 'public', 2048);
                }
                $data['documents'] = $uploadedDocs;
            }

            $application->update($data);

            LogHelper::updated('leave_application', $application->id, $application->company_id, "Leave updated");
            
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
     * Delete application (soft delete)
     */
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
    /**
     * Restore soft deleted leave application
     */
    public function restoreApplication(int $id): LeaveApplication
    {
        DB::beginTransaction();
        try {
            $application = LeaveApplication::withTrashed()->find($id);

            if (!$application) {
                throw ApiException::notFound('Leave Application');
            }

            $application->restore();
            
            LogHelper::restored('leave_application', $application->id, $application->company_id, "Leave restored for {$application->duration} days");

            Log::info('Leave application restored successfully', ['id' => $id]);
            
            DB::commit();
            return $application;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore leave application');
        }
    }

    /**
     * Permanently delete application and files
     */
    public function forceDeleteApplication(int $id): bool
    {
        DB::beginTransaction();
        try {
            $application = LeaveApplication::withTrashed()->find($id);
            if (!$application) throw ApiException::notFound('Leave Application');

            // Delete files from storage
            if (!empty($application->documents)) {
                foreach ($application->documents as $path) {
                    FileUploadHelper::delete($path);
                }
            }

            $application->forceDelete();
            LogHelper::forceDeleted('leave_application', $id, $application->company_id, "Permanently deleted");

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete');
        }
    }

    /**
     * Update Status (Approve/Cancel)
     */
    public function updateStatus(int $id, int $newStatusValue): LeaveApplication
    {
        DB::beginTransaction();
        try {
            $application = $this->getApplicationById($id);
            
            if ($newStatusValue == Status::Approved->value && $application->status != Status::Approved->value) {
                
                if ($application->assign_leave_id) {
                    $policy = AssignLeaveType::find($application->assign_leave_id);

                    if ($policy) {
                        $usedDays = LeaveApplication::where('employee_id', $application->employee_id)
                            ->where('leave_type_id', $application->leave_type_id)
                            ->where('status', Status::Approved->value)
                            ->where('id', '!=', $id)
                            ->sum('duration');

                        if (($usedDays + $application->duration) > $policy->leave_count) {
                            throw ApiException::badRequest('Insufficient leave balance.');
                        }
                    }
                } 
            }

            $application->update(['status' => $newStatusValue]);
            
            $statusLabel = Status::from($newStatusValue)->label();
            LogHelper::statusChanged('leave_application', $application->id, $application->company_id, "Status: {$statusLabel}");

            DB::commit();
            return $application;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e; 
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update status');
        }
    }
}
<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class LeaveTypeService
{
    /**
     * Get all Leave Types with filtering and pagination
     */
    public function getAllLeaveTypes(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeaveType::query();

            // Status Filter
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Name or Short Code
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('short_code', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting - Default by display_order as per your UI
            $sortBy = $filters['sort_by'] ?? 'display_order';
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching leave types: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch leave types');
        }
    }
    /**
     * Get Leave Type by ID
     */
    public function getLeaveTypeById(int $id): LeaveType
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            throw ApiException::notFound('Leave Type');
        }

        return $leaveType;
    }
    /**
     * Create a new Leave Type
     */
    public function createLeaveType(array $data): LeaveType
    {
        DB::beginTransaction();
        try {
            $leaveType = LeaveType::create($data);

            LogHelper::created('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name);
            DB::commit();
            Log::info('Leave type created successfully', ['id' => $leaveType->id]);

            return $leaveType;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError('Failed to create Leave Type');
        }
    }
    /**
     * Update Leave Type with DB Transaction
     */
    public function updateLeaveType(int $id, array $data): LeaveType
    {
        DB::beginTransaction();
        try {
            $leaveType = $this->getLeaveTypeById($id);
            $leaveType->update($data);

            LogHelper::updated('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name);
            Log::info('Leave Type updated successfully', ['id' => $id]);

            DB::commit();
            return $leaveType->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Leave Type');
        }
    }
    /**
     * Soft Delete Leave Type
     */
    public function deleteLeaveType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leaveType = $this->getLeaveTypeById($id);
            $leaveType->delete();

            LogHelper::deleted('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name);

            DB::commit();
            Log::info('Leave Type soft deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to delete Leave Type');
        }
    }

    /**
     * Restore Leave Type
     */
    public function restoreLeaveType(int $id): LeaveType
    {
        DB::beginTransaction();
        try {
            $leaveType = LeaveType::withTrashed()->find($id);

            if (!$leaveType) {
                throw ApiException::notFound('Leave Type');
            }

            $leaveType->restore();
            LogHelper::restored('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name);
            DB::commit();
            Log::info('Leave Type restored', ['id' => $id]);
            return $leaveType;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Leave Type');
        }
    }
    /**
     * Permanently delete Leave Type (Force Delete)
     */
    public function forceDeleteLeaveType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $leaveType = LeaveType::withTrashed()->find($id);

            if (!$leaveType) {
                throw ApiException::notFound('Leave Type');
            }

            $leaveType->forceDelete();

            LogHelper::forceDeleted('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name);
            Log::info('Leave Type permanently deleted', ['id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Leave Type');
        }
    }

    /**
     * Toggle status with DB Transaction
     */
    public function toggleStatus(int $id): LeaveType
    {
        DB::beginTransaction();
        try {
            $leaveType = $this->getLeaveTypeById($id);
            $currentStatus = Status::from($leaveType->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $leaveType->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('leave_type', $leaveType->id, $leaveType->company_id, $leaveType->name . ' to ' . $newStatus->label());

            DB::commit();
            return $leaveType;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave Type status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}

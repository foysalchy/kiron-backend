<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\AssignLeaveType;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class AssignLeaveService
{
    /**
     * Get all assigned leaves with filtering and pagination
     */
    public function getAllAssignments(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = AssignLeaveType::with(['position', 'leaveType']);

            // Status Filter (Including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Position Name
            if (isset($filters['search'])) {
                $query->whereHas('position', function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%");
                });
            }

            // Filter by specific position
            if (isset($filters['position_id'])) {
                $query->where('position_id', $filters['position_id']);
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching assigned leaves: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch assignments');
        }
    }

    /**
     * Get Assignment by ID
     */
    public function getAssignmentById(int $id): AssignLeaveType
    {
        $assignment = AssignLeaveType::with(['position', 'leaveType'])->find($id);

        if (!$assignment) {
            throw ApiException::notFound('Assignment');
        }

        return $assignment;
    }

    /**
     * Store Assignment (Sync Logic for UI)
     */
    public function storeAssignment(array $data): bool
    {
        DB::beginTransaction();
        try {
            $positionId = $data['position_id'];
            $companyId  = $data['company_id'];

            // delete existing assignments for the position
            AssignLeaveType::where('position_id', $positionId)
                           ->where('company_id', $companyId)
                           ->forceDelete();

            foreach ($data['leaves'] as $leave) {
                AssignLeaveType::create([
                    'company_id'    => $companyId,
                    'position_id'   => $positionId,
                    'leave_type_id' => $leave['leave_type_id'],
                    'leave_count'   => $leave['leave_count'],
                    'status'        => $data['status'] ?? 1
                ]);
            }

            LogHelper::updated('assign_leave_type', $positionId, $companyId, 'Leaves assigned to position');
            DB::commit();
            Log::info('Leaves assigned successfully', ['position_id' => $positionId, 'company_id' => $companyId]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to assign leaves');
        }
    }

    /**
     * Update a single assignment
     */
    public function updateAssignment(int $id, array $data): AssignLeaveType
    {
        DB::beginTransaction();
        try {
            $assignment = $this->getAssignmentById($id);
            $assignment->update($data);

            LogHelper::updated('assign_leave_type', $assignment->id, $assignment->company_id, 'Assignment updated');
            DB::commit();
            Log::info('Assignment updated successfully', ['assignment_id' => $id]);
            return $assignment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update assignment');
        }
    }

    /**
     * Soft Delete Assignment
     */
    public function deleteAssignment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $assignment = $this->getAssignmentById($id);
            $assignment->delete();

            LogHelper::deleted('assign_leave_type', $assignment->id, $assignment->company_id, 'Assignment soft deleted');
            DB::commit();
            Log::info('Assignment soft deleted', ['assignment_id' => $id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete assignment');
        }
    }

    /**
     * Restore Assignment
     */
    public function restoreAssignment(int $id): AssignLeaveType
    {
        DB::beginTransaction();
        try {
            $assignment = AssignLeaveType::withTrashed()->find($id);

            if (!$assignment) {
                throw ApiException::notFound('Assignment');
            }

            $assignment->restore();
            LogHelper::restored('assign_leave_type', $assignment->id, $assignment->company_id, 'Assignment restored');
            DB::commit();
            Log::info('Assignment restored successfully', ['assignment_id' => $id]);
            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore assignment');
        }
    }

    /**
     * Force Delete Assignment
     */
    public function forceDeleteAssignment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $assignment = AssignLeaveType::withTrashed()->find($id);

            if (!$assignment) {
                throw ApiException::notFound('Assignment');
            }

            $assignment->forceDelete();
            LogHelper::forceDeleted('assign_leave_type', $assignment->id, $assignment->company_id, 'Assignment permanently deleted');
            DB::commit();
            Log::info('Assignment permanently deleted', ['assignment_id' => $id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment force deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete assignment');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): AssignLeaveType
    {
        DB::beginTransaction();
        try {
            $assignment = $this->getAssignmentById($id);
            $currentStatus = Status::from($assignment->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $assignment->update(['status' => $newStatus->value]);
            LogHelper::statusChanged('assign_leave_type', $assignment->id, $assignment->company_id, 'Status toggled');

            DB::commit();
            Log::info('Assignment status toggled', ['assignment_id' => $id, 'new_status' => $newStatus->value]);
            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}

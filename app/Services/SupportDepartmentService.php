<?php 

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\SupportDepartment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class SupportDepartmentService
{
    /**
     * Get all departments with filtering and pagination
     */
    public function getAllDepartments(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SupportDepartment::query();

            // Status Filter 
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search Filter
            if (!empty($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate 
                ? $query->paginate($filters['per_page'] ?? 15) 
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching support departments: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch departments');
        }
    }
    public function getDepartmentById(int $id):SupportDepartment
    {
        $department = SupportDepartment::find($id);
        if (!$department) {
            throw ApiException::notFound('Support Department');
        }
        return $department;
    }
    public function createDepartment(array $data):SupportDepartment
    {
        DB::beginTransaction();
        try {
            $department = SupportDepartment::create($data);
            DB::commit();
            Log::info('Department created successfully', ['id' => $department->id]);
            return $department;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw ApiException::serverError('Failed to create department');
        }
    }
    public function updateDepartment(int $id, array $data): SupportDepartment
    {
        DB::beginTransaction();
        try {
            $department = $this->getDepartmentById($id);
            $department->update($data);
            DB::commit();
            return $department->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department update failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to update department');
        }
    }
    /**
     * Soft Delete Department
     */
    public function deleteDepartment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $department = $this->getDepartmentById($id);
            $department->delete();

            LogHelper::deleted('support_departments', $department->id, $department->company_id, $department->name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete department');
        }
    }

    /**
     * Restore Soft Deleted Department
     */
    public function restoreDepartment(int $id): SupportDepartment
    {
        DB::beginTransaction();
        try {
            $department = SupportDepartment::withTrashed()->find($id);
            if (!$department) {
                throw ApiException::notFound('Support Department');
            }

            $department->restore();
            LogHelper::restored('support_departments', $department->id, $department->company_id, $department->name);
            DB::commit();
            return $department;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore department');
        }
    }

    /**
     * Permanent Delete
     */
    public function forceDeleteDepartment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $department = SupportDepartment::withTrashed()->find($id);
            if (!$department) {
                throw ApiException::notFound('Support Department');
            }

            $department->forceDelete();
            LogHelper::forceDeleted('support_departments', $id, $department->company_id, $department->name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete department');
        }
    }
    public function toggleStatus(int $id): SupportDepartment
    {
        DB::beginTransaction();
        try {
            $department = $this->getDepartmentById($id);
            $currentStatus = Status::from($department->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $department->update(['status' => $newStatus->value]);
            
            LogHelper::statusChanged('support_departments', $department->id, $department->company_id, $department->name . ' new status ' . $newStatus->label());
            
            DB::commit();
            return $department;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
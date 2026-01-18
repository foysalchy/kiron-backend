<?php

namespace App\Services;

use App\Models\Department;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DepartmentService
{
    /**
     * Get all departments with optional pagination
     */
    public function getAllDepartments(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Department::query();

            if (isset($filters['status']) && $filters['status'] !== "") {
                $query->where('status', (int)$filters['status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('in_charge', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch departments');
        }
    }

    /**
     * Get department by ID
     */
    public function getDepartmentById(int $id): Department
    {
        $department = Department::find($id);
        if (!$department) {
            throw ApiException::notFound('Department');
        }
        return $department;
    }

    /**
     * Create a new department
     */
    public function createDepartment(array $data): Department
    {
        try {
            $department = Department::create($data);
            LogHelper::created('department', $department->id, $department->company_id);
            return $department;
        } catch (\Exception $e) {
            Log::error('Department creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create department');
        }
    }

    /**
     * Update department
     */
    public function updateDepartment(int $id, array $data): Department
    {
        try {
            $department = $this->getDepartmentById($id);
            $department->update($data);

            LogHelper::updated('department', $department->id, $department->company_id);
            return $department->fresh();
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Department update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update department');
        }
    }

    /**
     * Delete department
     */
    public function deleteDepartment(int $id): bool
    {
        try {
            $department = $this->getDepartmentById($id);
            $department->delete();

            LogHelper::deleted('department', $department->id, $department->company_id);
            return true;
        } catch (\Exception $e) {
            Log::error('Department deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete department');
        }
    }

    /**
     * Restore department
     */
    public function restoreDepartment(int $id): Department
    {
        try {
            $department = Department::withTrashed()->find($id);
            if (!$department) {
                throw ApiException::notFound('Department');
            }
            $department->restore();
            LogHelper::restored('department', $department->id, $department->company_id);
            return $department;
        } catch (\Exception $e) {
            Log::error('Department restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }
    /**
     * Permanently delete department
     */
    public function forceDeleteDepartment(int $id): bool
    {
        try {
            $department =Department::withTrashed()->find($id);

            if (!$department) {
                throw ApiException::notFound('Department');
            }

            $department->forceDelete();
            LogHelper::forceDeleted('department_value', $department->id, $department->company_id);

            Log::info('Department permanently deleted', ['department_id' => $id]);

            return true;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Department permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete department');
        }
    }
     /**
     * Toggle department status
     */
    public function toggleStatus(int $id): Department
    {
        try {
            $department = $this->getDepartmentById($id);
            $department->update(['status' => !$department->status]);
            LogHelper::statusChanged('department', $department->id, $department->company_id);
            Log::info('Department status toggled', ['department_id' => $id]);

            return $department;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Department status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle department status');
        }
    }
}

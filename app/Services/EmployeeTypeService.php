<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\EmployeeType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmployeeTypeService
{
    /**
     * Get all employee types with optional pagination
     */
    public function getAllEmployeeTypes(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = EmployeeType::query();

            // Status Filter
            if (isset($filters['status']) && $filters['status'] !== "") {
                $query->where('status', (int)$filters['status']);
            }

            // Search by Name
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('type_name', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching employee types: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch employee types');
        }
    }

    /**
     * Get employee type by ID
     */
    public function getEmployeeTypeById(int $id): EmployeeType
    {
        $employeeType = EmployeeType::find($id);
        if (!$employeeType) {
            throw ApiException::notFound('Employee Type');
        }
        return $employeeType;
    }

    /**
     * Create a new employee type
     */
    public function createEmployeeType(array $data): EmployeeType
    {
        try {
            $employeeType = EmployeeType::create($data);

            LogHelper::created('employee_type', $employeeType->id, $employeeType->company_id);
            Log::info('Employee Type created successfully', ['type_id' => $employeeType->id]);

            return $employeeType;
        } catch (\Exception $e) {
            Log::error('Employee Type creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create employee type');
        }
    }

    /**
     * Update employee type
     */
    public function updateEmployeeType(int $id, array $data): EmployeeType
    {
        try {
            $employeeType = $this->getEmployeeTypeById($id);
            $employeeType->update($data);

            LogHelper::updated('employee_type', $employeeType->id, $employeeType->company_id);
            Log::info('Employee Type Updated Successfully', ['type_id' => $employeeType->id]);

            return $employeeType->fresh();
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Employee Type update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update employee type');
        }
    }

    /**
     * Delete employee type (soft delete)
     */
    public function deleteEmployeeType(int $id): bool
    {
        try {
            $employeeType = $this->getEmployeeTypeById($id);
            $employeeType->delete();

            LogHelper::deleted('employee_type', $employeeType->id, $employeeType->company_id);
            Log::info('Employee Type deleted successfully', ['type_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Employee Type deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete employee type');
        }
    }

    /**
     * Restore soft deleted employee type
     */
    public function restoreEmployeeType(int $id): EmployeeType
    {
        try {
            $employeeType = EmployeeType::withTrashed()->find($id);
            if (!$employeeType) {
                throw ApiException::notFound('Employee Type');
            }
            $employeeType->restore();

            LogHelper::restored('employee_type', $employeeType->id, $employeeType->company_id);
            Log::info('Employee Type restored successfully', ['type_id' => $id]);

            return $employeeType;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Employee Type restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore employee type');
        }
    }

    /**
     * Permanently delete an employee type
     */
    public function forceDeleteEmployeeType(int $id): bool
    {
        try {
            $employeeType = EmployeeType::withTrashed()->find($id);
            if (!$employeeType) {
                throw ApiException::notFound('Employee Type');
            }
            $employeeType->forceDelete();

            LogHelper::forceDeleted('employee_type', $id, $employeeType->company_id);
            Log::info('Employee Type permanently deleted', ['type_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Employee Type permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete employee type');
        }
    }

    /**
     * Toggle employee type status (Active/Inactive)
     */
    public function toggleStatus(int $id): EmployeeType
    {
        try {
            $employeeType = $this->getEmployeeTypeById($id);

            $newStatus = $employeeType->status == 1 ? 0 : 1;
            $employeeType->update(['status' => $newStatus]);

            LogHelper::statusChanged('employee_type', $employeeType->id, $employeeType->company_id);
            Log::info('Employee Type status toggled', ['type_id' => $id, 'new_status' => $newStatus]);

            return $employeeType;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Employee Type status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle employee type status');
        }
    }
}

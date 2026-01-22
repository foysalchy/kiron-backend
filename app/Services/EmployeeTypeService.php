<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\EmployeeType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
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
        DB::beginTransaction();
        try {
            $employeeType = EmployeeType::create($data);

            LogHelper::created('employee_type', $employeeType->id, $employeeType->company_id, $employeeType->type_name);
            Log::info('Employee Type created successfully', ['type_id' => $employeeType->id]);
            DB::commit();
            return $employeeType;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create employee type');
        }
    }

    /**
     * Update employee type
     */
    public function updateEmployeeType(int $id, array $data): EmployeeType
    {
        DB::beginTransaction();
        try {
            $employeeType = $this->getEmployeeTypeById($id);
            $employeeType->update($data);

            LogHelper::updated('employee_type', $employeeType->id, $employeeType->company_id, $employeeType->type_name);
            Log::info('Employee Type Updated Successfully', ['type_id' => $employeeType->id]);
            DB::commit();
            return $employeeType->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update employee type');
        }
    }

    /**
     * Delete employee type (soft delete)
     */
    public function deleteEmployeeType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $employeeType = $this->getEmployeeTypeById($id);
            $employeeType->delete();

            LogHelper::deleted('employee_type', $employeeType->id, $employeeType->company_id, $employeeType->type_name);
            Log::info('Employee Type deleted successfully', ['type_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete employee type');
        }
    }

    /**
     * Restore soft deleted employee type
     */
    public function restoreEmployeeType(int $id): EmployeeType
    {
        DB::beginTransaction();
        try {
            $employeeType = EmployeeType::withTrashed()->find($id);
            if (!$employeeType) {
                throw ApiException::notFound('Employee Type');
            }
            $employeeType->restore();

            LogHelper::restored('employee_type', $employeeType->id, $employeeType->company_id, $employeeType->type_name);
            Log::info('Employee Type restored successfully', ['type_id' => $id]);
            DB::commit();
            return $employeeType;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore employee type');
        }
    }

    /**
     * Permanently delete an employee type
     */
    public function forceDeleteEmployeeType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $employeeType = EmployeeType::withTrashed()->find($id);
            if (!$employeeType) {
                throw ApiException::notFound('Employee Type');
            }
            $employeeType->forceDelete();

            LogHelper::forceDeleted('employee_type', $id, $employeeType->company_id, $employeeType->type_name);
            Log::info('Employee Type permanently deleted', ['type_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete employee type');
        }
    }

    /**
     * Toggle employee type status (Active/Inactive)
     */
    public function toggleStatus(int $id): EmployeeType
    {
        DB::beginTransaction();
        try {
            $employeeType = $this->getEmployeeTypeById($id);

            $currentStatus = Status::from($employeeType->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $employeeType->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('employee_type', $employeeType->id, $employeeType->company_id, $employeeType->type_name . ' new status ' . $newStatus->label());
            Log::info('Employee Type status toggled', ['type_id' => $id, 'new_status' => $newStatus->label()]);

            DB::commit();
            return $employeeType;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Type status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle employee type status');
        }
    }
}

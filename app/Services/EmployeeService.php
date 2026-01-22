<?php

namespace App\Services;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    /**
     * Get all employees with filtering and pagination
     */
    public function getAllEmployees(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Employee::with(['department', 'jobTitle', 'officeLocation', 'employeeType']);

            // Filters
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }

            if (!empty($filters['employee_type_id'])) {
                $query->where('employee_type_id', $filters['employee_type_id']);
            }
            if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
                $query->whereBetween('joining_date', [$filters['from_date'], $filters['to_date']]);
            }

            // Search by Name, Email or Phone
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching employees: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch employees');
        }
    }

    /**
     * Get office employee by ID
     */
    public function getEmployeeById(int $id): Employee
    {
        $employee = Employee::find($id);

        if (!$employee) {
            throw ApiException::notFound('Employee');
        }

        return $employee;
    }

    /**
     * Create a new employee
     */
    public function createEmployee(array $data): Employee
    {
        DB::beginTransaction();

        try {
            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'employees/images',
                    'public',
                    2048
                );
            }

            $employee = Employee::create($data);
            LogHelper::created('employee', $employee->id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name);

            DB::commit();
            Log::info('Employee created successfully', ['employee_id' => $employee->id]);

            return $employee;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Employee creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create employee');
        }
    }

    /**
     * Update employee
     */
    public function updateEmployee(int $id, array $data): Employee
    {
        DB::beginTransaction();

        try {
            $employee = $this->getEmployeeById($id);

            // Handle image replacement
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $employee->image,
                    'employees/images'
                );
            }

            $employee->update($data);
            LogHelper::updated('employee', $employee->id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name);

            DB::commit();
            Log::info('Employee updated successfully', ['employee_id' => $employee->id]);

            return $employee->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Employee update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update employee');
        }
    }

    /**
     * Delete employee (Soft Delete)
     */
    public function deleteEmployee(int $id): bool
    {
        DB::beginTransaction();
        try {
            $employee = $this->getEmployeeById($id);
            $employee->delete();
            LogHelper::deleted('employee', $id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name);

            DB::commit();
            Log::info('Employee deleted successfully', ['employee_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete employee');
        }
    }

    /**
     * Restore employee
     */
    public function restoreEmployee(int $id): Employee
    {
        DB::beginTransaction();
        try {
            $employee = Employee::withTrashed()->find($id);
            if (!$employee) throw ApiException::notFound('Employee');

            $employee->restore();
            LogHelper::restored('employee', $id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name);
            DB::commit();
            return $employee;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore employee');
        }
    }

    /**
     * Permanent delete employee and image
     */
    public function forceDeleteEmployee(int $id): bool
    {
        DB::beginTransaction();
        try {
            $employee = Employee::withTrashed()->find($id);
            if (!$employee) throw ApiException::notFound('Employee');

            // Delete actual image file
            if ($employee->image) {
                FileUploadHelper::delete($employee->image);
            }

            $employee->forceDelete();
            LogHelper::forceDeleted('employee', $id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete employee');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): Employee
    {
        DB::beginTransaction();
        try {
            $employee = $this->getEmployeeById($id);
            $currentStatus = Status::from($employee->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $employee->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('employee', $id, $employee->company_id, $employee->first_name . ' ' . $employee->last_name . ' new status ' . $newStatus->label());
            DB::commit();
            return $employee;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle employee status');
        }
    }
}

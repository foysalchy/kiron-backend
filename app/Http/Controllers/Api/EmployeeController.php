<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ){}
    public function index(Request $request):JsonResponse
    {
    $filters = [
            'status'             => $request->query('status'),
            'department_id'      => $request->query('department_id'),
            'office_location_id' => $request->query('office_location_id'),
            'search'             => $request->query('search'),
            'sort_by'            => $request->query('sort_by', 'created_at'),
            'sort_order'         => $request->query('sort_order', 'desc'),
            'per_page'           => $request->query('per_page', 15),
        ];

        $data = $this->employeeService->getAllEmployees($filters, true);

        return ResponseHelper::success($data, 'Employees retrieved successfully');
    }
    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $data = $this->employeeService->createEmployee($request->validated());

        return ResponseHelper::success($data, 'Employee created successfully', 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->employeeService->getEmployeeById($id);

        return ResponseHelper::success($data, 'Employee retrieved successfully');
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $data = $this->employeeService->updateEmployee($id, $request->validated());

        return ResponseHelper::success($data, 'Employee updated successfully');
    }

    /**
     * Remove the specified employee (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->employeeService->deleteEmployee($id);

        return ResponseHelper::success(null, 'Employee deleted successfully');
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->employeeService->restoreEmployee($id);

        return ResponseHelper::success($data, 'Employee restored successfully');
    }

    /**
     * Permanently delete an employee.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->employeeService->forceDeleteEmployee($id);

        return ResponseHelper::success(null, 'Employee permanently deleted');
    }

    /**
     * Toggle the status of an employee.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->employeeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Employee status updated successfully');
    }
}

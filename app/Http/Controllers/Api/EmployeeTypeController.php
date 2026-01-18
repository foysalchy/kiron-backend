<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeTypeRequest;
use App\Http\Requests\UpdateEmployeeTypeRequest;
use App\Services\EmployeeTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeTypeController extends Controller
{
    public function __construct(
        protected EmployeeTypeService $employeeTypeService
    ){}
    /**
     * Display a listing of employee types.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->employeeTypeService->getAllEmployeeTypes($filters, true);

        return ResponseHelper::success($data, 'Employee types retrieved successfully');
    }

    /**
     * Store a newly created employee type.
     */
    public function store(StoreEmployeeTypeRequest $request): JsonResponse
    {
        $data = $this->employeeTypeService->createEmployeeType($request->validated());

        return ResponseHelper::success($data, 'Employee type created successfully', 201);
    }

    /**
     * Display the specified employee type.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->employeeTypeService->getEmployeeTypeById($id);

        return ResponseHelper::success($data, 'Employee type retrieved successfully');
    }

    /**
     * Update the specified employee type.
     */
    public function update(UpdateEmployeeTypeRequest $request, int $id): JsonResponse
    {
        $data = $this->employeeTypeService->updateEmployeeType($id, $request->validated());

        return ResponseHelper::success($data, 'Employee type updated successfully');
    }

    /**
     * Remove the specified employee type (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->employeeTypeService->deleteEmployeeType($id);

        return ResponseHelper::success(null, 'Employee type deleted successfully');
    }

    /**
     * Restore a soft-deleted employee type.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->employeeTypeService->restoreEmployeeType($id);

        return ResponseHelper::success($data, 'Employee type restored successfully');
    }

    /**
     * Permanently delete an employee type.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->employeeTypeService->forceDeleteEmployeeType($id);

        return ResponseHelper::success(null, 'Employee type permanently deleted');
    }

    /**
     * Toggle the status of an employee type.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->employeeTypeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Employee type status updated successfully');
    }
}

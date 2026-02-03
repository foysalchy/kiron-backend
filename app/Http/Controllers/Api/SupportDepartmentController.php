<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportDepartmentRequest;
use App\Http\Requests\UpdateSupportDepartmentRequest;
use App\Services\SupportDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportDepartmentController extends Controller
{
    public function __construct(protected SupportDepartmentService $departmentService) 
    {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->departmentService->getAllDepartments($filters);
        return ResponseHelper::success($data, 'Support departments retrieved successfully');
    }

    public function store(StoreSupportDepartmentRequest $request): JsonResponse
    {
        $data = $this->departmentService->createDepartment($request->validated());

        return ResponseHelper::success($data, 'Support department created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->departmentService->getDepartmentById($id);

        return ResponseHelper::success($data, 'Support department retrieved successfully');
    }

    public function update(UpdateSupportDepartmentRequest $request, int $id): JsonResponse
    {
        $data = $this->departmentService->updateDepartment($id, $request->validated());

        return ResponseHelper::success($data, 'Support department update successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->departmentService->deleteDepartment($id);
        return ResponseHelper::success(null, 'Support department deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->departmentService->restoreDepartment($id);

        return ResponseHelper::success($data, 'Support department restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->departmentService->forceDeleteDepartment($id);

        return ResponseHelper::success(null, 'Support department permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->departmentService->toggleStatus($id);

        return ResponseHelper::success($data, 'Support department status updated successfully');
    }
}

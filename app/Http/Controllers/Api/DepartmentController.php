<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'            => $request->query('status'),
            'parent_department' => $request->query('parent_department'),
            'in_charge'         => $request->query('in_charge'),
            'search'            => $request->query('search'),
            'sort_by'           => $request->query('sort_by', 'created_at'),
            'sort_order'        => $request->query('sort_order', 'desc'),
            'per_page'          => $request->query('per_page', 15),
        ];

        $data = $this->departmentService->getAllDepartments($filters, true);

        return ResponseHelper::success($data, 'Departments retrieved successfully');
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $data = $this->departmentService->createDepartment($request->validated());

        return ResponseHelper::success($data, 'Department created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->departmentService->getDepartmentById($id);

        return ResponseHelper::success($data, 'Department retrieved successfully');
    }

    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        $data = $this->departmentService->updateDepartment($id, $request->validated());

        return ResponseHelper::success($data, 'Department updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->departmentService->deleteDepartment($id);

        return ResponseHelper::success(null, 'Department deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->departmentService->restoreDepartment($id);

        return ResponseHelper::success($data, 'Department restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->departmentService->forceDeleteDepartment($id);

        return ResponseHelper::success(null, 'Department permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->departmentService->toggleStatus($id);

        return ResponseHelper::success($data, 'Department status updated successfully');
    }
}

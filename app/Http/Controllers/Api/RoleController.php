<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->roleService->getAllRoles($filters, true);
        return ResponseHelper::success($data, 'Roles retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $data = $this->roleService->createRole($validated);
        return ResponseHelper::success($data, 'Role created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->roleService->getRoleById($id);
        return ResponseHelper::success($data, 'Role retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $data = $this->roleService->updateRole($id, $validated);
        return ResponseHelper::success($data, 'Role updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->roleService->deleteRole($id);
        return ResponseHelper::success(null, 'Role deleted successfully');
    }
}

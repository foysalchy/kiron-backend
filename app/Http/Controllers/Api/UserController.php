<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'select'     => $request->query('select'),
            'with'       => $request->query('with'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'role'       => $request->query('role'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->userService->getAllUsers($filters);

        return ResponseHelper::success($data, 'Users retrieved successfully');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $this->userService->createUser($request->validated());

        return ResponseHelper::success($data, 'User created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->userService->getUserById($id);

        return ResponseHelper::success($data, 'User retrieved successfully');
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = $this->userService->updateUser($id, $request->validated());

        return ResponseHelper::success($data, 'User updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return ResponseHelper::success(null, 'User deleted successfully');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->userService->toggleStatus($id);

        return ResponseHelper::success($data, 'User status updated successfully');
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductGroupRequest;
use App\Http\Requests\UpdateProductGroupRequest;
use App\Services\productGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function __construct(
        protected productGroupService $groupService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->groupService->getAllGroups($filters, true);
        return ResponseHelper::success($data, 'Groups retrieved successfully');
    }

    public function store(StoreProductGroupRequest $request): JsonResponse
    {
        $data = $this->groupService->createGroup($request->validated());
        return ResponseHelper::success($data, 'Product Group created successfully', 201);
    }
    
    public function update(UpdateProductGroupRequest $request, int $id): JsonResponse
    {
        $data = $this->groupService->updateGroup($id, $request->validated());
        return ResponseHelper::success($data, 'Product Group updated successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->groupService->getGroupWithProducts($id);
        return ResponseHelper::success($data, 'Group details retrieved successfully');
    }

    public function removeCustomer(int $id, int $customerId): JsonResponse
    {
        $data = $this->groupService->removeCustomerFromGroup($id, $customerId);
        return ResponseHelper::success($data, 'Customer removed from group successfully');
    }
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->groupService->toggleStatus($id);
        return ResponseHelper::success($data, 'Group status updated successfully');
    }
    public function toggleFrontend(int $id): JsonResponse
    {
        $data = $this->groupService->toggleFrontend($id);
        return ResponseHelper::success($data, 'Group status updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->groupService->deleteGroup($id);
        return ResponseHelper::success(null, 'Group deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $this->groupService->restoreGroup($id);
        return ResponseHelper::success(null, 'Product Group restored successfully');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->groupService->forceDeleteGroup($id);
        return ResponseHelper::success(null, 'Product Group permanently deleted');
    }

    // Special Endpoint for Dynamic Criteria
    public function fetchProductsByCriteria(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'parameters' => 'nullable|array'
        ]);

        $data = $this->groupService->getProductsByCriteria(
            $request->query('type'),
            $request->query('parameters', [])
        );

        return ResponseHelper::success($data, 'Customers retrieved successfully');
    }
}

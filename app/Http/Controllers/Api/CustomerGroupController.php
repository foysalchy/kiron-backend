<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerGroupRequest;
use App\Services\CustomerGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function __construct(
        protected CustomerGroupService $groupService
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

        $columns = ['*'];
        if ($request->has('select')) {
            $select = $request->query('select');
            $columns = is_string($select) ? explode(',', $select) : $select;
        }

        $data = $this->groupService->getAllGroups($filters, true, $columns);
        return ResponseHelper::success($data, 'Groups retrieved successfully');
    }

    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        $data = $this->groupService->createGroup($request->validated());
        return ResponseHelper::success($data, 'Customer Group created successfully', 201);
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->groupService->getGroupWithCustomers($id);
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

    public function destroy(int $id): JsonResponse
    {
        $this->groupService->deleteGroup($id);
        return ResponseHelper::success(null, 'Group deleted successfully');
    }

    // Special Endpoint for Dynamic Criteria
    public function fetchCustomersByCriteria(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'parameters' => 'nullable|array'
        ]);

        $data = $this->groupService->getCustomersByCriteria(
            $request->query('type'),
            $request->query('parameters', [])
        );

        return ResponseHelper::success($data, 'Customers retrieved successfully');
    }
}

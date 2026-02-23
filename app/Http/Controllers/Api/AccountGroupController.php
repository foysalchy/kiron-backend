<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreAccountGroupRequest;
use App\Http\Requests\Accounts\UpdateAccountGroupRequest;
use App\Services\AccountGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountGroupController extends Controller
{
    public function __construct(protected AccountGroupService $accountGroupService) {}

    /**
     * Display a listing of the account groups.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'          => $request->query('status'),
            'search'          => $request->query('search'),
            'sort_by'         => $request->query('sort_by', 'created_at'),
            'sort_order'      => $request->query('sort_order', 'desc'),
            'per_page'        => $request->query('per_page', 15),
        ];

        $data = $this->accountGroupService->getAllGroups($filters, true);

        return ResponseHelper::success($data, 'Account groups retrieved successfully');
    }

    /**
     * Store a newly created account group.
     */
    public function store(StoreAccountGroupRequest $request): JsonResponse
    {
        $data = $this->accountGroupService->createGroup($request->validated());

        return ResponseHelper::success($data, 'Account group created successfully', 201);
    }

    /**
     * Display the specified account group.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->accountGroupService->getGroupById($id);

        return ResponseHelper::success($data, 'Account group retrieved successfully');
    }

    /**
     * Update the specified account group.
     */
    public function update(UpdateAccountGroupRequest $request, int $id): JsonResponse
    {
        $data = $this->accountGroupService->updateGroup($id, $request->validated());

        return ResponseHelper::success($data, 'Account group updated successfully');
    }

    /**
     * Soft delete the account group.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->accountGroupService->deleteGroup($id);

        return ResponseHelper::success(null, 'Account group deleted successfully');
    }

    /**
     * Restore soft-deleted account group.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->accountGroupService->restoreGroup($id);

        return ResponseHelper::success($data, 'Account group restored successfully');
    }

    /**
     * Permanently delete the account group.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->accountGroupService->forceDeleteGroup($id);

        return ResponseHelper::success(null, 'Account group permanently deleted');
    }

    /**
     * Toggle status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->accountGroupService->toggleStatus($id);

        return ResponseHelper::success($data, 'Account group status updated successfully');
    }
}

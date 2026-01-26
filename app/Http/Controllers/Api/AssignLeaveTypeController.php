<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignLeaveRequest;
use App\Http\Requests\UpdateAssignLeaveRequest;
use App\Services\AssignLeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignLeaveTypeController extends Controller
{
    public function __construct(
        protected AssignLeaveService $assignLeaveService
    ) {} 

    /**
     * Display a listing of assigned leaves.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'      => $request->query('status'),
            'search'      => $request->query('search'), 
            'position_id' => $request->query('position_id'),
            'sort_by'     => $request->query('sort_by', 'created_at'),
            'sort_order'  => $request->query('sort_order', 'desc'),
            'per_page'    => $request->query('per_page', 15),
        ];

        $data = $this->assignLeaveService->getAllAssignments($filters, true);

        return ResponseHelper::success($data, 'Assigned leaves retrieved successfully');
    }

    /**
     * Store or Sync leaves for a position.
     */
    public function store(StoreAssignLeaveRequest $request): JsonResponse
    {
        $data = $this->assignLeaveService->storeAssignment($request->validated());

        return ResponseHelper::created($data, 'Leaves assigned successfully to the position');
    }

    /**
     * Display the specified assignment.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->assignLeaveService->getAssignmentById($id);

        return ResponseHelper::success($data, 'Assignment details retrieved successfully');
    }

    /**
     * Update a specific assignment.
     */
    public function update(UpdateAssignLeaveRequest $request, int $id): JsonResponse
    {
        $data = $this->assignLeaveService->updateAssignment($id, $request->validated());

        return ResponseHelper::success($data, 'Assignment updated successfully');
    }

    /**
     * Soft delete an assignment.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->assignLeaveService->deleteAssignment($id);

        return ResponseHelper::success(null, 'Assignment deleted successfully');
    }

    /**
     * Restore a soft deleted assignment.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->assignLeaveService->restoreAssignment($id);

        return ResponseHelper::success($data, 'Assignment restored successfully');
    }

    /**
     * Permanently delete an assignment.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->assignLeaveService->forceDeleteAssignment($id);

        return ResponseHelper::success(null, 'Assignment permanently deleted');
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->assignLeaveService->toggleStatus($id);

        return ResponseHelper::success($data, 'Assignment status updated successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Services\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $leaveTypeService)
    {
    }
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'display_order'), 
            'sort_order' => $request->query('sort_order', 'asc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->leaveTypeService->getAllLeaveTypes($filters, true);

        return ResponseHelper::success($data, 'Leave types retrieved successfully');
    }

    /**
     * Store a newly created leave type.
     */
    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $leaveType = $this->leaveTypeService->createLeaveType($request->validated());

        return ResponseHelper::created($leaveType, 'Leave type created successfully');
    }

    /**
     * Display the specified leave type.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leaveTypeService->getLeaveTypeById($id);

        return ResponseHelper::success($data, 'Leave type retrieved successfully');
    }

    /**
     * Update the specified leave type.
     */
    public function update(UpdateLeaveTypeRequest $request, int $id): JsonResponse
    {
        $data = $this->leaveTypeService->updateLeaveType($id, $request->validated());

        return ResponseHelper::success($data, 'Leave type updated successfully');
    }

    /**
     * Soft delete the leave type.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leaveTypeService->deleteLeaveType($id);

        return ResponseHelper::success(null, 'Leave type deleted successfully');
    }

    /**
     * Restore a soft deleted leave type.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leaveTypeService->restoreLeaveType($id);

        return ResponseHelper::success($data, 'Leave type restored successfully');
    }

    /**
     * Permanently delete the leave type.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leaveTypeService->forceDeleteLeaveType($id);

        return ResponseHelper::success(null, 'Leave type permanently deleted');
    }

    /**
     * Toggle Leave Type status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->leaveTypeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Leave type status updated successfully');
    }
}

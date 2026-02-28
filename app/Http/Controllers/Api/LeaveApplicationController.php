<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveApplicationRequest;
use App\Http\Requests\UpdateLeaveApplicationRequest;
use App\Services\LeaveApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApplicationController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $leaveApplicationService
    ) {}

    /**
     * Display a listing of the leave applications.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'      => $request->query('status'),
            'search'      => $request->query('search'),
            'sort_by'     => $request->query('sort_by', 'created_at'),
            'sort_order'  => $request->query('sort_order', 'desc'),
            'per_page'    => $request->query('per_page', 15),
            'employee_id' => $request->query('employee_id'),
        ];

        $data = $this->leaveApplicationService->getAllApplications($filters, true);

        return ResponseHelper::success($data, 'Leave applications retrieved successfully');
    }

    /**
     * Store a newly created leave application.
     */
    public function store(StoreLeaveApplicationRequest $request): JsonResponse
    {
        $data = $this->leaveApplicationService->createApplication($request->validated());

        return ResponseHelper::success($data, 'Leave application submitted successfully', 201);
    }

    /**
     * Display the specified leave application.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leaveApplicationService->getApplicationById($id);

        return ResponseHelper::success($data, 'Leave application retrieved successfully');
    }

    /**
     * Update the specified leave application.
     */
    public function update(UpdateLeaveApplicationRequest $request, int $id): JsonResponse
    {
        $data = $this->leaveApplicationService->updateApplication($id, $request->validated());

        return ResponseHelper::success($data, 'Leave application updated successfully');
    }

    /**
     * Remove the specified leave application (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leaveApplicationService->deleteApplication($id);

        return ResponseHelper::success(null, 'Leave application deleted successfully');
    }

    /**
     * Restore the specified soft-deleted leave application.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leaveApplicationService->restoreApplication($id);

        return ResponseHelper::success($data, 'Leave application restored successfully');
    }

    /**
     * Permanently delete the specified leave application.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leaveApplicationService->forceDeleteApplication($id);

        return ResponseHelper::success(null, 'Leave application permanently deleted');
    }

    /**
     * Update the status of the leave application (Approve/Reject).
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $data = $this->leaveApplicationService->updateStatus($id, $request->status);

        return ResponseHelper::success($data, 'Leave application status updated successfully');
    }
    public function leaveBalances(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'department_id', 'employee_id', 'per_page']);

        $balances = $this->leaveApplicationService->getLeaveBalances($filters);

        return ResponseHelper::success($balances, 'Leave balances retrieved successfully');
    }
}

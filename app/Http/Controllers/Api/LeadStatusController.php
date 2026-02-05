<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadStatusRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Services\LeadStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    public function __construct(protected LeadStatusService $leadStatusService)
    {
    }

    /**
     * Display a listing of lead statuses.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'asc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->leadStatusService->getAllLeadStatuses($filters, true);

        return ResponseHelper::success($data, 'Lead statuses retrieved successfully');
    }

    /**
     * Store a newly created lead status.
     */
    public function store(StoreLeadStatusRequest $request): JsonResponse
    {
        $leadStatus = $this->leadStatusService->createLeadStatus($request->validated());

        return ResponseHelper::created($leadStatus, 'Lead status created successfully');
    }

    /**
     * Display the specified lead status.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->leadStatusService->getLeadStatusById($id);

        return ResponseHelper::success($data, 'Lead status retrieved successfully');
    }

    /**
     * Update the specified lead status.
     */
    public function update(UpdateLeadStatusRequest $request, int $id): JsonResponse
    {
        $data = $this->leadStatusService->updateLeadStatus($id, $request->validated());

        return ResponseHelper::success($data, 'Lead status updated successfully');
    }

    /**
     * Soft delete the lead status.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->leadStatusService->deleteLeadStatus($id);

        return ResponseHelper::success(null, 'Lead status deleted successfully');
    }

    /**
     * Restore a soft deleted lead status.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->leadStatusService->restoreLeadStatus($id);

        return ResponseHelper::success($data, 'Lead status restored successfully');
    }

    /**
     * Permanently delete the lead status.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->leadStatusService->forceDeleteLeadStatus($id);

        return ResponseHelper::success(null, 'Lead status permanently deleted');
    }

    /**
     * Toggle Lead Status status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->leadStatusService->toggleStatus($id);

        return ResponseHelper::success($data, 'Lead status status updated successfully');
    }
}

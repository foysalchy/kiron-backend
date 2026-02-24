<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreRequisitionRequest, UpdateRequisitionRequest};
use App\Services\RequisitionService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class RequisitionController extends Controller
{
    public function __construct(
        protected RequisitionService $requisitionService
    ) {}

    /**
     * Get all requisitions with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'user_id' => $request->query('user_id'),
            'status' => $request->query('status'),
            'request_date_from' => $request->query('request_date_from'),
            'request_date_to' => $request->query('request_date_to'),
            'need_date_from' => $request->query('need_date_from'),
            'need_date_to' => $request->query('need_date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'request_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->requisitionService->getAllRequisitions($filters);

        return ResponseHelper::success($data, 'Requisitions retrieved successfully');
    }

    /**
     * Create new requisition
     */
    public function store(StoreRequisitionRequest $request): JsonResponse
    {
        $data = $this->requisitionService->createRequisition($request->validated());

        return ResponseHelper::success($data, 'Requisition created successfully', 201);
    }

    /**
     * Get single requisition
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->requisitionService->getRequisitionById($id);

        return ResponseHelper::success($data, 'Requisition retrieved successfully');
    }

    /**
     * Update requisition
     */
    public function update(UpdateRequisitionRequest $request, int $id): JsonResponse
    {
        $data = $this->requisitionService->updateRequisition($id, $request->validated());

        return ResponseHelper::success($data, 'Requisition updated successfully');
    }

    /**
     * Delete requisition
     */
    public function destroy(int $id): JsonResponse
    {
        $this->requisitionService->deleteRequisition($id);

        return ResponseHelper::success(null, 'Requisition deleted successfully');
    }

    /**
     * Approve requisition
     */
    public function approve(int $id): JsonResponse
    {
        $data = $this->requisitionService->approveRequisition($id);

        return ResponseHelper::success($data, 'Requisition approved successfully');
    }

    /**
     * Reject requisition
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $data = $this->requisitionService->rejectRequisition($id, $request->reject_reason);

        return ResponseHelper::success($data, 'Requisition rejected successfully');
    }

    /**
     * Complete requisition
     */
    public function complete(int $id): JsonResponse
    {
        $data = $this->requisitionService->completeRequisition($id);

        return ResponseHelper::success($data, 'Requisition completed successfully');
    }

    /**
     * Change requisition status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Status::Pending->value,
                    Status::Approved->value,
                    Status::Completed->value,
                    Status::Cancelled->value,
                ]),
            ],
            'reject_reason' => ['nullable', 'string'],
        ]);

        $data = $this->requisitionService->changeStatus(
            $id,
            $request->status,
            $request->reject_reason
        );

        return ResponseHelper::success($data, 'Requisition status updated successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->requisitionService->restoreRequisition($id);

        return ResponseHelper::success($data, 'Requisition restored successfully');
    }

    /**
     * Permanently delete a requisition
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->requisitionService->forceDeleteRequisition($id);

        return ResponseHelper::success(null, 'Requisition permanently deleted successfully');
    }
}

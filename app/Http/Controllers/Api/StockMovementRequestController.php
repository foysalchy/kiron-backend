<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreStockMovementRequestRequest, UpdateStockMovementRequestRequest};
use App\Services\StockMovementRequestService;
use Illuminate\Http\{JsonResponse, Request};

class StockMovementRequestController extends Controller
{
    public function __construct(
        protected StockMovementRequestService $requestService
    ) {}

    /**
     * Get all stock movement requests
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'source_warehouse_id' => $request->query('source_warehouse_id'),
            'destination_warehouse_id' => $request->query('destination_warehouse_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'request_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->requestService->getAllRequests($filters, true);

        return ResponseHelper::success($data, 'Stock movement requests retrieved successfully');
    }

    /**
     * Create stock movement request
     */
    public function store(StoreStockMovementRequestRequest $request): JsonResponse
    {
        $data = $this->requestService->createRequest($request->validated());

        return ResponseHelper::success($data, 'Stock movement request created successfully', 201);
    }

    /**
     * Get single stock movement request
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->requestService->getRequestById($id);

        return ResponseHelper::success($data, 'Stock movement request retrieved successfully');
    }

    /**
     * Update stock movement request
     */
    public function update(UpdateStockMovementRequestRequest $request, int $id): JsonResponse
    {
        $data = $this->requestService->updateRequest($id, $request->validated());

        return ResponseHelper::success($data, 'Stock movement request updated successfully');
    }

    /**
     * Delete stock movement request
     */
    public function destroy(int $id): JsonResponse
    {
        $this->requestService->deleteRequest($id);

        return ResponseHelper::success(null, 'Stock movement request deleted successfully');
    }

    /**
     * Approve stock movement request
     */
    public function approve(int $id): JsonResponse
    {
        $data = $this->requestService->approveRequest($id);

        return ResponseHelper::success($data, 'Stock movement request approved successfully');
    }

    /**
     * Reject stock movement request
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $data = $this->requestService->rejectRequest($id, $request->rejection_reason);

        return ResponseHelper::success($data, 'Stock movement request rejected successfully');
    }

    /**
     * Cancel stock movement request
     */
    public function cancel(int $id): JsonResponse
    {
        $data = $this->requestService->cancelRequest($id);

        return ResponseHelper::success($data, 'Stock movement request cancelled successfully');
    }

    /**
     * Convert to stock movement
     */
    public function convertToMovement(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'movement_date' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.batch_number' => 'nullable|string',
            'items.*.source_bin_id' => 'nullable|exists:bins,id',
            'items.*.destination_bin_id' => 'nullable|exists:bins,id',
            'items.*.serial_numbers' => 'nullable|array',
        ]);

        $data = $this->requestService->convertToMovement($id, $request->all());

        return ResponseHelper::success($data, 'Request converted to stock movement successfully', 201);
    }
}
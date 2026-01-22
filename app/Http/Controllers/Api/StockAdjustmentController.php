<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreStockAdjustmentRequest, UpdateStockAdjustmentRequest};
use App\Services\StockAdjustmentService;
use Illuminate\Http\{JsonResponse, Request};

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockAdjustmentService $adjustmentService
    ) {}

    /**
     * Get all stock adjustments
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'adjustment_reason' => $request->query('adjustment_reason'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'adjustment_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->adjustmentService->getAllAdjustments($filters, true);

        return ResponseHelper::success($data, 'Stock adjustments retrieved successfully');
    }

    /**
     * Create stock adjustment
     */
    public function store(StoreStockAdjustmentRequest $request): JsonResponse
    {
        $data = $this->adjustmentService->createAdjustment($request->validated());

        return ResponseHelper::success($data, 'Stock adjustment created successfully', 201);
    }

    /**
     * Get single stock adjustment
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->adjustmentService->getAdjustmentById($id);

        return ResponseHelper::success($data, 'Stock adjustment retrieved successfully');
    }

    /**
     * Update stock adjustment
     */
    public function update(UpdateStockAdjustmentRequest $request, int $id): JsonResponse
    {

        $data = $this->adjustmentService->updateAdjustment($id, $request->validated());

        return ResponseHelper::success($data, 'Stock adjustment updated successfully');
    }

    /**
     * Delete stock adjustment
     */
    public function destroy(int $id): JsonResponse
    {
        $this->adjustmentService->deleteAdjustment($id);

        return ResponseHelper::success(null, 'Stock adjustment deleted successfully');
    }

    /**
     * Restore stock adjustment
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->adjustmentService->restoreAdjustment($id);

        return ResponseHelper::success($data, 'Stock adjustment restored successfully');
    }

    /**
     * Force delete stock adjustment
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->adjustmentService->forceDeleteAdjustment($id);

        return ResponseHelper::success(null, 'Stock adjustment permanently deleted successfully');
    }
}
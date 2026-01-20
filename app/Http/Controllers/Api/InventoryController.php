<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreStockMovementRequest, UpdateStockMovementRequest};
use App\Services\InventoryService;
use Illuminate\Http\{JsonResponse, Request};

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Get inventory summary
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'brand_id' => $request->query('brand_id'),
            'warehouse_id' => $request->query('warehouse_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->inventoryService->getInventorySummary($filters, true);

        return ResponseHelper::success($data, 'Inventory summary retrieved successfully');
    }

    /**
     * Get all stock movements
     */
    public function movements(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'source_warehouse_id' => $request->query('source_warehouse_id'),
            'destination_warehouse_id' => $request->query('destination_warehouse_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'movement_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->inventoryService->getAllMovements($filters, true);

        return ResponseHelper::success($data, 'Stock movements retrieved successfully');
    }

    /**
     * Create stock movement
     */
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $data = $this->inventoryService->createMovement($request->validated());

        return ResponseHelper::success($data, 'Stock movement created successfully', 201);
    }

    /**
     * Get single stock movement
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->inventoryService->getMovementById($id);

        return ResponseHelper::success($data, 'Stock movement retrieved successfully');
    }

    /**
     * Update stock movement
     */
    public function update(UpdateStockMovementRequest $request, int $id): JsonResponse
    {
        $data = $this->inventoryService->updateMovement($id, $request->validated());

        return ResponseHelper::success($data, 'Stock movement updated successfully');
    }

    /**
     * Delete stock movement
     */
    public function destroy(int $id): JsonResponse
    {
        $this->inventoryService->deleteMovement($id);

        return ResponseHelper::success(null, 'Stock movement deleted successfully');
    }

    /**
     * Approve stock movement
     */
    public function approve(int $id): JsonResponse
    {
        $data = $this->inventoryService->approveMovement($id);

        return ResponseHelper::success($data, 'Stock movement approved successfully');
    }

    /**
     * Cancel stock movement
     */
    public function cancel(int $id): JsonResponse
    {
        $data = $this->inventoryService->cancelMovement($id);

        return ResponseHelper::success($data, 'Stock movement cancelled successfully');
    }

    /**
     * Get products by warehouse
     */
    public function getProductsByWarehouse(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        $data = $this->inventoryService->getProductsByWarehouse($request->warehouse_id);

        return ResponseHelper::success($data, 'Products retrieved successfully');
    }

    /**
     * Get bins by warehouse
     */
    public function getBinsByWarehouse(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        $data = $this->inventoryService->getBinsByWarehouse($request->warehouse_id);

        return ResponseHelper::success($data, 'Bins retrieved successfully');
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WarehouseInventoryService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class WarehouseInventoryController extends Controller
{
    public function __construct(
        protected WarehouseInventoryService $warehouseInventoryService
    ) {}

    /**
     * Get warehouse dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $data = $this->warehouseInventoryService->getWarehouseDashboard();

            return ResponseHelper::success($data, 'Warehouse dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get stats for a specific warehouse
     */
    public function stats(int $warehouseId): JsonResponse
    {
        try {
            $stats = $this->warehouseInventoryService->getWarehouseStats($warehouseId);

            return ResponseHelper::success($stats, 'Warehouse stats retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get top products by warehouse
     */
    public function topProducts(Request $request, int $warehouseId): JsonResponse
    {
        $limit = $request->query('limit', 10);

        try {
            $products = $this->warehouseInventoryService->getTopProductsByWarehouse($warehouseId, $limit);

            return ResponseHelper::success($products, 'Top products retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get recent movements for warehouse
     */
    public function recentMovements(Request $request, int $warehouseId): JsonResponse
    {
        $limit = $request->query('limit', 5);

        try {
            $movements = $this->warehouseInventoryService->getRecentMovements($warehouseId, $limit);

            return ResponseHelper::success($movements, 'Recent movements retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
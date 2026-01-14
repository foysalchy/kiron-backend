<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(
        protected WarehouseService $warehouseService
    )
    {}
    public function index(Request $request): JsonResponse
    {
        $filters = [

            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->warehouseService->getAllWarehouses($filters);
        return ResponseHelper::success($data,'Warehouse retrieved successfully');
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $data = $this->warehouseService->createWarehouse($request->validated());

        return ResponseHelper::success($data, 'Warehouse created successfully', 201);
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->warehouseService->getWarehouseById($id);

        return ResponseHelper::success($data, 'Warehouse retrieved successfully');
    }
    public function update(UpdateWarehouseRequest $request, int $id): JsonResponse
    {
        $data = $this->warehouseService->updateWarehouse($id, $request->validated());

        return ResponseHelper::success($data, 'Warehouse updated successfully');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->warehouseService->deleteWarehouse($id);
        return ResponseHelper::success(null, 'Warehouse deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->warehouseService->restoreWarehouse($id);

        return ResponseHelper::success($data, 'Warehouse restore successfully');
    }
    public function forceDestroy(int $id): JsonResponse
    {
        $this->warehouseService->forceDeleteWarehouse($id);

        return ResponseHelper::success(null, 'Warehouse permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->warehouseService->toggleStatus($id);

        return ResponseHelper::success($data, 'Warehouse status updated successfully');
    }
}

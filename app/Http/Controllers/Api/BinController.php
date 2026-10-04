<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreBinRequest, UpdateBinRequest, BulkCreateBinRequest};
use App\Services\BinService;
use Illuminate\Http\{JsonResponse, Request};

class BinController extends Controller
{
    public function __construct(
        protected BinService $binService
    ) {}

    /**
     * Get all bins
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'area_id' => $request->query('area_id'),
            'rack_id' => $request->query('rack_id'),
            'cell_id' => $request->query('cell_id'),
            'select'     => $request->query('select'),
            'with'       => $request->query('with'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->binService->getAllBins($filters, true);

        return ResponseHelper::success($data, 'Bins retrieved successfully');
    }

    /**
     * Create new bin
     */
    public function store(StoreBinRequest $request): JsonResponse
    {
        $data = $this->binService->createBin($request->validated());

        return ResponseHelper::success($data, 'Bin created successfully', 201);
    }

    /**
     * Get single bin
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->binService->getBinById($id);

        return ResponseHelper::success($data, 'Bin retrieved successfully');
    }

    /**
     * Update bin
     */
    public function update(UpdateBinRequest $request, int $id): JsonResponse
    {
        $data = $this->binService->updateBin($id, $request->validated());

        return ResponseHelper::success($data, 'Bin updated successfully');
    }

    /**
     * Delete bin
     */
    public function destroy(int $id): JsonResponse
    {
        $this->binService->deleteBin($id);

        return ResponseHelper::success(null, 'Bin deleted successfully');
    }

    /**
     * Change bin status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        $data = $this->binService->changeBinStatus($id, $request->status);

        return ResponseHelper::success($data, 'Bin status updated successfully');
    }

    /**
     * Get bins by warehouse
     */
    public function byWarehouse(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'active_only' => 'nullable|boolean'
        ]);

        $data = $this->binService->getBinsByWarehouse(
            $request->warehouse_id,
            $request->boolean('active_only', true)
        );

        return ResponseHelper::success($data, 'Bins retrieved successfully');
    }

    /**
     * Get bins by area
     */
    public function byArea(Request $request): JsonResponse
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'active_only' => 'nullable|boolean'
        ]);

        $data = $this->binService->getBinsByArea(
            $request->area_id,
            $request->boolean('active_only', true)
        );

        return ResponseHelper::success($data, 'Bins retrieved successfully');
    }

    /**
     * Get bins by rack
     */
    public function byRack(Request $request): JsonResponse
    {
        $request->validate([
            'rack_id' => 'required|exists:racks,id',
            'active_only' => 'nullable|boolean'
        ]);

        $data = $this->binService->getBinsByRack(
            $request->rack_id,
            $request->boolean('active_only', true)
        );

        return ResponseHelper::success($data, 'Bins retrieved successfully');
    }

  
}


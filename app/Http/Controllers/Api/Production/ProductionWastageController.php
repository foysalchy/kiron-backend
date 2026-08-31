<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionWastageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionWastageController extends Controller
{
    public function __construct(
        protected ProductionWastageService $wastageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'production_order_id' => $request->query('production_order_id'),
            'reason' => $request->query('reason'),
            'warehouse_id' => $request->query('warehouse_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->wastageService->getAll($filters, true);
        return ResponseHelper::success($data, 'Production wastages retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'nullable|exists:production_orders,id',
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'required|in:cutting_waste,damaged,defective,process_loss,expired,machine_error,other',
            'date' => 'nullable|date',
            'notes' => 'nullable|string',
            'adjust_stock' => 'nullable|boolean',
        ]);

        $data = $this->wastageService->create($validated);
        return ResponseHelper::success($data, 'Wastage recorded successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->wastageService->getById($id);
        return ResponseHelper::success($data, 'Wastage details retrieved successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->wastageService->delete($id);
        return ResponseHelper::success(null, 'Wastage deleted successfully');
    }
}

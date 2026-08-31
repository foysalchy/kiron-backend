<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionPlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionPlanningController extends Controller
{
    public function __construct(
        protected ProductionPlanningService $planService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'warehouse_id' => $request->query('warehouse_id'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->planService->getAll($filters, true);
        return ResponseHelper::success($data, 'Production plans retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_number' => 'nullable|string|max:100',
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'bill_of_material_id' => 'required|exists:bills_of_materials,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:draft,approved,converted_to_order,cancelled',
            'assigned_team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $this->planService->create($validated);
        return ResponseHelper::success($data, 'Production plan created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->planService->getById($id);
        return ResponseHelper::success($data, 'Production plan retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'plan_number' => 'nullable|string|max:100',
            'product_id' => 'sometimes|required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'bill_of_material_id' => 'sometimes|required|exists:bills_of_materials,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'planned_quantity' => 'sometimes|required|numeric|min:0.01',
            'unit' => 'sometimes|required|string|max:50',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:draft,approved,converted_to_order,cancelled',
            'assigned_team' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $this->planService->update($id, $validated);
        return ResponseHelper::success($data, 'Production plan updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->planService->delete($id);
        return ResponseHelper::success(null, 'Production plan deleted successfully');
    }

    public function convertToOrder(Request $request, int $id): JsonResponse
    {
        $additionalData = $request->all();
        $order = $this->planService->convertToOrder($id, $additionalData);
        return ResponseHelper::success($order, 'Production plan converted to order successfully', 201);
    }
}

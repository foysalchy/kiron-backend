<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function __construct(
        protected ProductionOrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'product_id' => $request->query('product_id'),
            'warehouse_id' => $request->query('warehouse_id'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->orderService->getAll($filters, true);
        return ResponseHelper::success($data, 'Production orders retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|string|max:100',
            'production_plan_id' => 'nullable|exists:production_plans,id',
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'bill_of_material_id' => 'required|exists:bills_of_materials,id',
            'raw_material_warehouse_id' => 'required|exists:warehouses,id',
            'finished_goods_warehouse_id' => 'required|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:draft,planned,in_progress,quality_check,completed,paused,cancelled',
            'allow_partial_production' => 'nullable|boolean',
            'planned_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $this->orderService->create($validated);
        return ResponseHelper::success($data, 'Production order created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->orderService->getById($id);
        return ResponseHelper::success($data, 'Production order retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'planned_quantity' => 'nullable|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'raw_material_warehouse_id' => 'nullable|exists:warehouses,id',
            'finished_goods_warehouse_id' => 'nullable|exists:warehouses,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'allow_partial_production' => 'nullable|boolean',
            'planned_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $this->orderService->update($id, $validated);
        return ResponseHelper::success($data, 'Production order updated successfully');
    }

    public function checkStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bill_of_material_id' => 'required|exists:bills_of_materials,id',
            'planned_quantity' => 'required|numeric|min:0.01',
            'raw_material_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $result = $this->orderService->checkStockAvailability(
            (int)$validated['bill_of_material_id'],
            (float)$validated['planned_quantity'],
            (int)$validated['raw_material_warehouse_id']
        );

        return ResponseHelper::success($result, 'Stock availability checked');
    }

    public function start(Request $request, int $id): JsonResponse
    {
        $options = [
            'force_start' => $request->boolean('force_start', false),
            'auto_consume' => $request->boolean('auto_consume', true),
        ];

        $data = $this->orderService->startProduction($id, $options);
        return ResponseHelper::success($data, 'Production started successfully');
    }

    public function progressStage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:production_stages,id',
            'notes' => 'nullable|string',
        ]);

        $data = $this->orderService->progressStage($id, (int)$validated['stage_id'], $validated);
        return ResponseHelper::success($data, 'Stage progressed successfully');
    }

    public function pause(Request $request, int $id): JsonResponse
    {
        $reason = $request->input('reason');
        $data = $this->orderService->pauseProduction($id, $reason);
        return ResponseHelper::success($data, 'Production paused');
    }

    public function resume(int $id): JsonResponse
    {
        $data = $this->orderService->resumeProduction($id);
        return ResponseHelper::success($data, 'Production resumed');
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'produced_quantity' => 'required|numeric|min:0.01',
            'rejected_quantity' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $data = $this->orderService->completeProduction($id, $validated);
        return ResponseHelper::success($data, 'Production completed successfully');
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $reason = $request->input('reason');
        $data = $this->orderService->cancelProduction($id, $reason);
        return ResponseHelper::success($data, 'Production cancelled');
    }
}

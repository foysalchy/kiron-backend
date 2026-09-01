<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\BillOfMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    public function __construct(
        protected BillOfMaterialService $bomService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'product_id' => $request->query('product_id'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->bomService->getAll($filters, true);
        return ResponseHelper::success($data, 'BOMs retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bom_number' => 'nullable|string|max:100',
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'version' => 'nullable|string|max:50',
            'production_quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'labor_cost' => 'nullable|numeric|min:0',
            'machine_cost' => 'nullable|numeric|min:0',
            'electricity_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'packaging_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,draft,archived',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.component_type' => 'nullable|in:raw_material,semi_finished',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        $data = $this->bomService->create($validated);
        return ResponseHelper::success($data, 'BOM created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->bomService->getById($id);
        return ResponseHelper::success($data, 'BOM retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'bom_number' => 'nullable|string|max:100',
            'product_id' => 'sometimes|required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'version' => 'nullable|string|max:50',
            'production_quantity' => 'sometimes|required|numeric|min:0.01',
            'unit' => 'sometimes|required|string|max:50',
            'labor_cost' => 'nullable|numeric|min:0',
            'machine_cost' => 'nullable|numeric|min:0',
            'electricity_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'packaging_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,draft,archived',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.component_type' => 'nullable|in:raw_material,semi_finished',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        $data = $this->bomService->update($id, $validated);
        return ResponseHelper::success($data, 'BOM updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bomService->delete($id);
        return ResponseHelper::success(null, 'BOM deleted successfully');
    }

    public function clone(Request $request, int $id): JsonResponse
    {
        $newVersion = $request->input('version');
        $data = $this->bomService->clone($id, $newVersion);
        return ResponseHelper::success($data, 'BOM cloned successfully', 201);
    }
}

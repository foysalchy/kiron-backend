<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionQualityCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionQualityCheckController extends Controller
{
    public function __construct(
        protected ProductionQualityCheckService $qcService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'production_order_id' => $request->query('production_order_id'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->qcService->getAll($filters, true);
        return ResponseHelper::success($data, 'Quality checks retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'inspected_quantity' => 'required|numeric|min:0.01',
            'passed_quantity' => 'required|numeric|min:0',
            'failed_quantity' => 'nullable|numeric|min:0',
            'defective_quantity' => 'nullable|numeric|min:0',
            'inspector_id' => 'nullable|exists:users,id',
            'inspection_date' => 'nullable|date',
            'status' => 'required|in:pending,passed,failed,partially_passed',
            'defect_details' => 'nullable|array',
            'defective_warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $data = $this->qcService->create($validated);
        return ResponseHelper::success($data, 'Quality check recorded successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->qcService->getById($id);
        return ResponseHelper::success($data, 'Quality check retrieved successfully');
    }
}

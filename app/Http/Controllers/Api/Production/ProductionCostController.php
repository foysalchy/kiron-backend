<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductionCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionCostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductionCost::with([
            'productionOrder.product',
            'productionOrder.rawMaterialWarehouse',
            'productionOrder.finishedGoodsWarehouse'
        ]);

        if ($request->filled('production_order_id')) {
            $query->where('production_order_id', $request->query('production_order_id'));
        }

        $data = $query->orderBy('created_at', 'desc')->paginate($request->query('per_page', 15));
        return ResponseHelper::success($data, 'Production costs retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $cost = ProductionCost::with([
            'productionOrder.product',
            'productionOrder.rawMaterialWarehouse',
            'productionOrder.finishedGoodsWarehouse',
            'productionOrder.consumptions.product',
            'productionOrder.wastages.product'
        ])->find($id);

        if (!$cost) {
            return ResponseHelper::error('Production cost record not found', 404);
        }

        return ResponseHelper::success($cost, 'Production cost details retrieved successfully');
    }
}

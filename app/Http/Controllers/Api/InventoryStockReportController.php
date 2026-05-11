<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\InventoryStockReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryStockReportController extends Controller
{
    public function __construct(
        private InventoryStockReportService $service
    ) {}



    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id'    => ['nullable', 'integer', 'exists:warehouses,id'],
            'brand_id'        => ['nullable', 'integer', 'exists:brands,id'],
            'mega_category_id' => ['nullable', 'integer'],
            'stock_filter'    => ['nullable', 'in:all,low,out'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->service->generate($request->only([
            'warehouse_id',
            'brand_id',
            'mega_category_id',
            'stock_filter',
            'start_date',
            'end_date',
        ]));

        return ResponseHelper::success($data);
    }
}

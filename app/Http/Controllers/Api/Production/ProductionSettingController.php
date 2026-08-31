<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionSettingController extends Controller
{
    public function __construct(
        protected ProductionSettingService $settingService
    ) {}

    public function show(): JsonResponse
    {
        $data = $this->settingService->getSettings();
        return ResponseHelper::success($data, 'Production settings retrieved successfully');
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_prefix' => 'nullable|string|max:20',
            'bom_prefix' => 'nullable|string|max:20',
            'plan_prefix' => 'nullable|string|max:20',
            'auto_consume_on_start' => 'nullable|boolean',
            'require_qc_before_completion' => 'nullable|boolean',
            'allow_over_consumption' => 'nullable|boolean',
            'default_raw_material_warehouse_id' => 'nullable|exists:warehouses,id',
            'default_finished_goods_warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $data = $this->settingService->updateSettings($validated);
        return ResponseHelper::success($data, 'Production settings updated successfully');
    }
}

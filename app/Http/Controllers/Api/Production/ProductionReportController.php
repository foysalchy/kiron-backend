<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function __construct(
        protected ProductionReportService $reportService
    ) {}

    public function productionReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'product_id', 'warehouse_id', 'status', 'per_page']);
        $data = $this->reportService->getProductionReport($filters);
        return ResponseHelper::success($data, 'Production report retrieved successfully');
    }

    public function materialConsumptionReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'product_id', 'warehouse_id', 'per_page']);
        $data = $this->reportService->getMaterialConsumptionReport($filters);
        return ResponseHelper::success($data, 'Material consumption report retrieved successfully');
    }

    public function costReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'per_page']);
        $data = $this->reportService->getCostReport($filters);
        return ResponseHelper::success($data, 'Cost report retrieved successfully');
    }

    public function efficiencyReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'per_page']);
        $data = $this->reportService->getEfficiencyReport($filters);
        return ResponseHelper::success($data, 'Efficiency report retrieved successfully');
    }
}

<?php

namespace App\Http\Controllers\Api\Production;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Production\ProductionReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionDashboardController extends Controller
{
    public function __construct(
        protected ProductionReportService $reportService
    ) {}

    public function stats(): JsonResponse
    {
        $data = $this->reportService->getDashboardStats();
        return ResponseHelper::success($data, 'Dashboard stats retrieved successfully');
    }

    public function charts(): JsonResponse
    {
        $data = $this->reportService->getDashboardCharts();
        return ResponseHelper::success($data, 'Dashboard charts retrieved successfully');
    }
}

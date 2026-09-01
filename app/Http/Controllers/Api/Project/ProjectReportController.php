<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    public function __construct(
        protected ProjectReportService $reportService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $data = $this->reportService->getProjectSummaryReport($request->all());
        return ResponseHelper::success($data, 'Project summary report retrieved');
    }

    public function materials(Request $request): JsonResponse
    {
        $data = $this->reportService->getMaterialReport($request->all());
        return ResponseHelper::success($data, 'Material consumption report retrieved');
    }

    public function labor(Request $request): JsonResponse
    {
        $data = $this->reportService->getLaborReport($request->all());
        return ResponseHelper::success($data, 'Labor report retrieved');
    }

    public function profitability(Request $request): JsonResponse
    {
        $data = $this->reportService->getProfitabilityReport($request->all());
        return ResponseHelper::success($data, 'Profitability report retrieved');
    }
}

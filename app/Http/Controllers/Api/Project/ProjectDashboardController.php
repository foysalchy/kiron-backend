<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectDashboardController extends Controller
{
    public function __construct(
        protected ProjectDashboardService $dashboardService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->dashboardService->getDashboardStats($request->all());
        return ResponseHelper::success($stats, 'Dashboard stats retrieved');
    }

    public function charts(Request $request): JsonResponse
    {
        $charts = $this->dashboardService->getDashboardCharts();
        return ResponseHelper::success($charts, 'Dashboard charts retrieved');
    }
}

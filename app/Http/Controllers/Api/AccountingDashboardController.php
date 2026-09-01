<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\AccountingDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingDashboardController extends Controller
{
    public function __construct(
        protected AccountingDashboardService $dashboardService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $period = $request->get('period', 'this_month');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $data = $this->dashboardService->getDashboardStats($companyId, $period, $customStart, $customEnd);
        return ResponseHelper::success($data, 'Finance dashboard stats retrieved successfully');
    }

    public function charts(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $data = $this->dashboardService->getDashboardCharts($companyId);
        return ResponseHelper::success($data, 'Finance dashboard charts retrieved successfully');
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $data = $this->dashboardService->getRecentActivity($companyId);
        return ResponseHelper::success($data, 'Finance dashboard recent activity retrieved successfully');
    }

    public function orderProfitability(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $period = $request->get('period', 'this_month');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $data = $this->dashboardService->getOrderProfitabilityAnalysis($companyId, $period, $customStart, $customEnd);
        return ResponseHelper::success($data, 'Order profitability analysis retrieved successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {}

    public function overview(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['nullable', 'in:today,yesterday,last_7_days,last_30_days,this_month,last_month'],
        ]);

        $data = $this->service->overview($request->period ?? 'last_30_days');

        return ResponseHelper::success($data);
    }
}
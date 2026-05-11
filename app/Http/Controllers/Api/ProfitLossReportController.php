<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\ProfitLossReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitLossReportController extends Controller
{
    public function __construct(
        private ProfitLossReportService $service
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after_or_equal:start_date'],
            'ads_expense' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data = $this->service->generate(
            $request->start_date,
            $request->end_date,
            (float) ($request->ads_expense ?? 0),
        );

        return ResponseHelper::success($data);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\BalanceSheetReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceSheetReportController extends Controller
{
    public function __construct(
        private BalanceSheetReportService $service
    ) {}



    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $data = $this->service->generate(
            $request->start_date,
            $request->end_date
        );

        return ResponseHelper::success($data);
    }
}

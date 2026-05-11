<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\SalesReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function __construct(
        private SalesReportService $service
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
        );

        return ResponseHelper::success($data);
    }
}

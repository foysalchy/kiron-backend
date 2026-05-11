<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\CustomerReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerReportController extends Controller
{
    public function __construct(
        private CustomerReportService $service
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'repeat_threshold' => ['nullable', 'integer', 'min:1'],
        ]);

        $data = $this->service->generate($request->only([
            'start_date',
            'end_date',
            'repeat_threshold',
        ]));
        return ResponseHelper::success($data);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\ProductWiseSalesReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductWiseSalesReportController extends Controller
{
    public function __construct(
        private ProductWiseSalesReportService $service
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'mega_category_id' => ['nullable', 'integer', 'exists:mega_categories,id'],
            'brand_id'         => ['nullable', 'integer', 'exists:brands,id'],
            'product_id'       => ['nullable', 'integer', 'exists:products,id'],
            'status_filter'    => ['nullable', 'in:delivered,all'],
            'sort_by'          => ['nullable', 'in:quantity,sales,profit'],
        ]);

        $data = $this->service->generate($request->all());

        return ResponseHelper::success($data);
    }
}
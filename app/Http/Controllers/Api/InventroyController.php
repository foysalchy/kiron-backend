<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\InventroyService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InventroyController extends Controller
{
    public function __construct(
        protected InventroyService $inventroyService
    ) {}


    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'brand_id' => $request->query('brand_id'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->inventroyService->getInventroySummary($filters, true);

        return ResponseHelper::success($data, 'Inventroy Summary retrieved successfully');
    }
}

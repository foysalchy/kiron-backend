<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMarketRequest;
use App\Services\MarketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function __construct(protected MarketService $marketService)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $data = $this->marketService->getMarketing();

        return ResponseHelper::success($data, 'Marketing settings updated successfully');
    }
    public function update(UpdateMarketRequest $request): JsonResponse
    {
        $market = $this->marketService->updateMarketTools($request->validated());

        return ResponseHelper::success($market, 'Marketing settings updated successfully');
    }
}

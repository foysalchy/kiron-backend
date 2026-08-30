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

    public function testCapi(Request $request): JsonResponse
    {
        $request->validate([
            'meta_access_token' => 'required|string',
            'facebook_pixel_id' => 'required|string',
            'test_event_code' => 'required|string',
        ]);

        $pixelId = $request->input('facebook_pixel_id');
        $accessToken = $request->input('meta_access_token');
        $testEventCode = $request->input('test_event_code');

        $url = "https://graph.facebook.com/v22.0/{$pixelId}/events";

        $response = \Illuminate\Support\Facades\Http::post($url, [
            'data' => [
                [
                    'event_name' => 'Purchase',
                    'event_time' => time(),
                    'action_source' => 'website',
                    'user_data' => [
                        'client_ip_address' => $request->ip(),
                        'client_user_agent' => $request->userAgent(),
                    ],
                    'custom_data' => [
                        'currency' => 'BDT',
                        'value' => 100,
                    ],
                ],
            ],
            'test_event_code' => $testEventCode,
            'access_token' => $accessToken,
        ]);

        if ($response->successful()) {
            return ResponseHelper::success($response->json(), 'Test event sent successfully. Check your Meta Events Manager.');
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send test event',
            'error' => $response->json(),
        ], 400);
    }
}

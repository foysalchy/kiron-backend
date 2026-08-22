<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WoocommerceWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WoocommerceWebhookController extends Controller
{
    public function __construct(
        protected WoocommerceWebhookService $webhookService
    ) {}

    public function handle(Request $request, int $settingId)
    {
        Log::info("WooCommerce Webhook received for setting ID: {$settingId}");
        
        $orderData = $request->all();

        try {
            $this->webhookService->handleOrderWebhook($settingId, $orderData);
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed: " . $e->getMessage());
            // Return 200 anyway so WooCommerce doesn't retry infinitely and disable the webhook.
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}

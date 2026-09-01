<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StatusMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PathaoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            // 1. Handle Webhook Integration Verification
            if (isset($payload['event']) && $payload['event'] === 'webhook_integration') {
                return response()->json([], 202)->header(
                    'X-Pathao-Merchant-Webhook-Integration-Secret',
                    'f3992ecc-59da-4cbe-a049-a13da2018d51'
                );
            }

            $consignmentId = $payload['consignment_id'] ?? null;
            $pathaoEvent = $payload['event'] ?? null;

            if (!$consignmentId || !$pathaoEvent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing consignment_id or event.'
                ], 400);
            }

            // Find the order where courier_info->consignment_id matches
            $order = Order::whereJsonContains('courier_info->consignment_id', $consignmentId)
                ->orWhereJsonContains('courier_info->consignment_id', (string)$consignmentId)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found for given consignment ID.'
                ], 404);
            }

            // Authenticate Webhook using X-PATHAO-Signature (if configured)
            $courier = \App\Models\Courier::where('company_id', $order->company_id)
                ->where('name', 'pathao')
                ->first();
                
            $savedToken = $courier->method_details['webhook_token'] ?? null;
            if ($savedToken) {
                $providedSignature = $request->header('X-PATHAO-Signature');
                if ($providedSignature !== $savedToken) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized. Invalid Signature.'
                    ], 401);
                }
            }

            // Find the reverse mapping: Kiron status that maps to this pathao event
            $mappings = StatusMapping::where('company_id', $order->company_id)->get();
            $kironStatus = null;

            foreach ($mappings as $mapping) {
                $providerMappings = $mapping->mappings ?? [];
                if (isset($providerMappings['pathao']) && $providerMappings['pathao'] === $pathaoEvent) {
                    $kironStatus = $mapping->self_status;
                    break;
                }
            }

            $statusChanged = false;
            if ($kironStatus !== null && $order->status !== $kironStatus) {
                $order->status = $kironStatus;
                $statusChanged = true;
            }

            // Process delivery fee and payment if it's delivered
            if ($pathaoEvent === 'order.delivered') {
                $deliveryFee = $payload['delivery_fee'] ?? null;
                if ($deliveryFee !== null) {
                    $courierInfo = is_array($order->courier_info) ? $order->courier_info : (json_decode($order->courier_info, true) ?: []);
                    $courierInfo['courier_charge'] = (float)$deliveryFee;
                    $courierInfo['delivery_charge'] = (float)$deliveryFee;
                    $order->courier_info = $courierInfo;
                }
            }

            $order->save();

            // Auto-post courier expense journal
            \App\Services\AutoAccountingService::postCourierExpenseJournal($order);

            // Trigger sync to WooCommerce if the status was changed by Pathao
            if ($statusChanged) {
                app(\App\Services\StatusSyncService::class)->syncOrderStatus($order);
            }

            Log::info("Pathao webhook processed for Order ID: {$order->id}, Consignment: {$consignmentId}, Event: {$pathaoEvent}");

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Pathao Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An internal error occurred.'
            ], 500);
        }
    }
}

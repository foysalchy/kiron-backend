<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StatusMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\ResponseHelper;

class SteadfastWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            // Only process delivery_status notifications for now
            if (!isset($payload['notification_type']) || $payload['notification_type'] !== 'delivery_status') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook received successfully. Notification type ignored.'
                ], 200);
            }

            $consignmentId = $payload['consignment_id'] ?? null;
            $steadfastStatus = $payload['status'] ?? null;
            $codAmount = $payload['cod_amount'] ?? 0;

            if (!$consignmentId || !$steadfastStatus) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing consignment_id or status.'
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

            // Authenticate Webhook using Bearer Token (if configured)
            $courier = \App\Models\Courier::where('company_id', $order->company_id)
                ->where('name', 'steadfast')
                ->first();
                
            $savedToken = $courier->method_details['webhook_token'] ?? null;
            if ($savedToken) {
                $providedToken = $request->bearerToken();
                if ($providedToken !== $savedToken) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized. Invalid Bearer Token.'
                    ], 401);
                }
            }

            // Find the reverse mapping: Kiron status that maps to this steadfast status
            $mappings = StatusMapping::where('company_id', $order->company_id)->get();
            $kironStatus = null;

            foreach ($mappings as $mapping) {
                $providerMappings = $mapping->mappings ?? [];
                if (isset($providerMappings['steadfast']) && $providerMappings['steadfast'] === $steadfastStatus) {
                    $kironStatus = $mapping->self_status;
                    break;
                }
            }

            $statusChanged = false;
            if ($kironStatus !== null && $order->status !== $kironStatus) {
                $order->status = $kironStatus;
                $statusChanged = true;
            }

            // If status is Delivered or partially delivered, update COD amount
            if (in_array($steadfastStatus, ['delivered', 'partial_delivered']) && $codAmount > 0) {
                $order->payment_amount = $codAmount;
                // If the payment amount >= grand_total, we could set payment_status to 2 (paid)
                // For now, we'll set it to 2 (paid) if there's a payment, or 1 (partial) if it's less
                if ($codAmount >= $order->grand_total) {
                    $order->payment_status = 2; // Paid
                } else {
                    $order->payment_status = 1; // Partial
                }
            }

            // Update courier_charge in courier_info from Steadfast (preserves customer's other_charges)
            $deliveryCharge = $payload['delivery_charge'] ?? null;
            if ($deliveryCharge !== null) {
                $courierInfo = is_array($order->courier_info) ? $order->courier_info : (json_decode($order->courier_info, true) ?: []);
                $courierInfo['courier_charge'] = (float)$deliveryCharge;
                $courierInfo['delivery_charge'] = (float)$deliveryCharge;
                $order->courier_info = $courierInfo;
            }

            $order->save();

            // Auto-post courier expense journal
            \App\Services\AutoAccountingService::postCourierExpenseJournal($order);

            // Trigger sync to WooCommerce if the status was changed by Steadfast
            if ($statusChanged) {
                app(\App\Services\StatusSyncService::class)->syncOrderStatus($order);
            }

            Log::info("Steadfast webhook processed for Order ID: {$order->id}, Consignment: {$consignmentId}, Status: {$steadfastStatus}");

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Steadfast Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An internal error occurred.'
            ], 500);
        }
    }
}

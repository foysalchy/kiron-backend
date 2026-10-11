<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use App\Models\StatusMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PathaoWebhookController extends Controller
{
    /**
     * Helper to return response with mandatory Pathao integration secret header.
     */
    protected function webhookResponse(array $data, int $statusCode = 200, ?string $secret = null)
    {
        $secret = $secret 
            ?: request()->header('X-Pathao-Merchant-Webhook-Integration-Secret')
            ?: request()->header('X-PATHAO-Signature')
            ?: 'f3992ecc-59da-4cbe-a049-a13da2018d51';

        return response()->json($data, $statusCode)->header(
            'X-Pathao-Merchant-Webhook-Integration-Secret',
            $secret
        );
    }

    public function handle(Request $request)
    {
        // 1. Log all incoming webhook requests immediately
        Log::info('Pathao Webhook: Incoming request', [
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
            'headers' => [
                'x-pathao-signature'                           => $request->header('X-PATHAO-Signature'),
                'x-pathao-merchant-webhook-integration-secret' => $request->header('X-Pathao-Merchant-Webhook-Integration-Secret'),
                'content-type'                                 => $request->header('Content-Type'),
            ],
            'payload' => $request->all(),
        ]);

        try {
            $payload = $request->all();
            $event = $payload['event'] ?? $request->query('event') ?? null;

            // Determine default secret
            $courier = Courier::where('name', 'pathao')->first();
            $defaultSecret = $courier->method_details['webhook_token'] 
                ?? $courier->method_details['webhook_secret'] 
                ?? 'f3992ecc-59da-4cbe-a049-a13da2018d51';

            $secret = $request->header('X-Pathao-Merchant-Webhook-Integration-Secret')
                ?? $request->header('X-PATHAO-Signature')
                ?? $defaultSecret;

            // 2. Handle Webhook Integration Verification Handshake / Ping
            if ($event === 'webhook_integration' || $request->isMethod('get')) {
                Log::info('Pathao Webhook: Verification responded successfully', ['secret' => $secret]);

                return $this->webhookResponse([
                    'status'  => 'success',
                    'message' => 'Webhook verified successfully.'
                ], 200, $secret);
            }

            // Extract Consignment ID, Event, Status, and Merchant Order ID
            $consignmentId = $payload['consignment_id'] 
                ?? $payload['consignmentId'] 
                ?? $payload['data']['consignment_id'] 
                ?? null;

            $pathaoEvent = $payload['event'] 
                ?? $payload['order_status'] 
                ?? $payload['status'] 
                ?? $payload['data']['event'] 
                ?? null;

            $orderStatus = $payload['order_status'] 
                ?? $payload['status'] 
                ?? null;

            $merchantOrderId = $payload['merchant_order_id'] 
                ?? $payload['merchantOrderId'] 
                ?? $payload['data']['merchant_order_id'] 
                ?? null;

            // If it's a test payload from Pathao (e.g. merchant_order_id is 'TEST' or consignment_id starts with 'DL')
            $isTest = ($merchantOrderId === 'TEST' || $merchantOrderId === 'TS-123' || $consignmentId === 'DL121224VS8TTJ');

            if (!$consignmentId && !$merchantOrderId) {
                Log::warning('Pathao Webhook: Missing consignment_id and merchant_order_id', ['payload' => $payload]);
                return $this->webhookResponse([
                    'status'  => 'error',
                    'message' => 'Missing consignment_id or merchant_order_id.'
                ], 400, $secret);
            }

            // 3. Find the order: by consignment_id or by merchant_order_id
            $order = null;

            if ($consignmentId) {
                $order = Order::where('courier_info->consignment_id', (string)$consignmentId)
                    ->orWhere('courier_info->consignment_id', $consignmentId)
                    ->orWhereJsonContains('courier_info->consignment_id', (string)$consignmentId)
                    ->orWhereJsonContains('courier_info->consignment_id', $consignmentId)
                    ->first();

                if (!$order) {
                    $order = Order::where('courier_info', 'like', '%"' . $consignmentId . '"%')->first();
                }
            }

            if (!$order && $merchantOrderId) {
                $order = Order::where('order_no', (string)$merchantOrderId)
                    ->orWhere('id', $merchantOrderId)
                    ->first();
            }

            // If order not found (Pathao test payload or non-existent order)
            if (!$order) {
                Log::warning('Pathao Webhook: Order not found in database', [
                    'consignment_id'    => $consignmentId,
                    'merchant_order_id' => $merchantOrderId,
                    'is_test'           => $isTest,
                    'payload'           => $payload
                ]);

                // Always return 200 with required header so Pathao webhook integration test succeeds
                return $this->webhookResponse([
                    'status'  => 'success',
                    'message' => 'Webhook received. Order acknowledged.'
                ], 200, $secret);
            }

            // 4. Authenticate Webhook using signature if configured
            $orderCourier = Courier::where('company_id', $order->company_id)
                ->where('name', 'pathao')
                ->first();
                
            if ($orderCourier) {
                $savedToken = $orderCourier->method_details['webhook_token'] 
                    ?? $orderCourier->method_details['webhook_secret'] 
                    ?? null;

                if ($savedToken) {
                    $providedSignature = $request->header('X-PATHAO-Signature')
                        ?? $request->header('X-Pathao-Signature')
                        ?? $request->header('X-Pathao-Merchant-Webhook-Integration-Secret');

                    if ($providedSignature && $providedSignature !== $savedToken) {
                        Log::warning('Pathao Webhook: Signature mismatch', [
                            'order_id'  => $order->id,
                            'provided'  => $providedSignature,
                            'expected'  => $savedToken
                        ]);

                        return $this->webhookResponse([
                            'status'  => 'error',
                            'message' => 'Unauthorized. Invalid Signature.'
                        ], 401, $secret);
                    }
                }
            }

            // 5. Find status mapping in database
            $mappings = StatusMapping::where('company_id', $order->company_id)->get();
            $kironStatus = null;

            // Search mappings by event or nested order_status
            foreach ($mappings as $mapping) {
                $providerMappings = $mapping->mappings ?? [];
                if (!empty($providerMappings['pathao'])) {
                    $mappedVal = strtolower(trim((string)$providerMappings['pathao']));
                    if (
                        ($pathaoEvent && $mappedVal === strtolower(trim((string)$pathaoEvent))) ||
                        ($orderStatus && $mappedVal === strtolower(trim((string)$orderStatus)))
                    ) {
                        $kironStatus = (int) $mapping->self_status;
                        break;
                    }
                }
            }

            // 6. Default fallback mapping if no custom StatusMapping configured
            if ($kironStatus === null) {
                $statusSlug = $payload['order_status_slug'] ?? null;
                $candidates = array_filter([$pathaoEvent, $orderStatus, $statusSlug]);

                foreach ($candidates as $cand) {
                    $norm = strtolower(str_replace([' ', '-', '.'], '_', trim((string)$cand)));
                    $norm = preg_replace('/^order_/', '', $norm); // normalize 'order_delivered' -> 'delivered'

                    $kironStatus = match ($norm) {
                        'delivered', 'order_delivered'                                  => Status::Delivered->value,
                        'returned', 'order_returned'                                    => Status::Returned->value,
                        'return_received', 'order_return_received'                      => Status::ReturnReceived->value,
                        'picked', 'pickup_completed', 'order_picked'                    => Status::HandovertoCourier->value,
                        'assigned_for_delivery', 'in_transit', 'order_in_transit'       => Status::InTransit->value,
                        'cancelled', 'canceled', 'order_cancelled'                      => Status::Cancelled->value,
                        'partial_delivery', 'order_partial_delivery'                    => Status::Delivered->value,
                        'hold', 'on_hold', 'order_on_hold'                              => Status::Hold->value,
                        default                                                         => null,
                    };

                    if ($kironStatus !== null) {
                        break;
                    }
                }
            }

            $statusChanged = false;
            if ($kironStatus !== null && (int)$order->status !== (int)$kironStatus) {
                $order->status = $kironStatus;
                $statusChanged = true;
            }

            // 7. Update courier_info
            $courierInfo = is_array($order->courier_info) 
                ? $order->courier_info 
                : (json_decode($order->courier_info, true) ?: []);

            $courierInfo['status'] = $orderStatus ?: $pathaoEvent;
            $courierInfo['updated_at'] = now()->toDateTimeString();

            // Process delivery fee
            $deliveryFee = $payload['delivery_fee'] ?? null;
            if ($deliveryFee !== null) {
                $courierInfo['courier_charge'] = (float)$deliveryFee;
                $courierInfo['delivery_charge'] = (float)$deliveryFee;
            }

            $order->courier_info = $courierInfo;
            $order->save();

            // Auto-post courier expense journal
            try {
                \App\Services\AutoAccountingService::postCourierExpenseJournal($order);
            } catch (\Throwable $e) {
                Log::error('Pathao Webhook: AutoAccounting error: ' . $e->getMessage());
            }

            // Trigger sync to WooCommerce if the status was changed
            if ($statusChanged) {
                try {
                    app(\App\Services\StatusSyncService::class)->syncOrderStatus($order);
                } catch (\Throwable $e) {
                    Log::error('Pathao Webhook: WooCommerce sync error: ' . $e->getMessage());
                }
            }

            Log::info("Pathao webhook processed successfully", [
                'order_id'       => $order->id,
                'consignment_id' => $consignmentId,
                'event'          => $pathaoEvent,
                'kiron_status'   => $kironStatus,
                'status_changed' => $statusChanged
            ]);

            return $this->webhookResponse([
                'status'  => 'success',
                'message' => 'Webhook received successfully.'
            ], 200, $secret);

        } catch (\Throwable $e) {
            Log::error('Pathao Webhook Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->webhookResponse([
                'status'  => 'error',
                'message' => 'An internal error occurred.'
            ], 500, $secret ?? null);
        }
    }
}

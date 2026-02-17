<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Support\Facades\{Http, Log, DB};
use Illuminate\Validation\ValidationException;

class PathaoService
{
    // protected string $baseUrl = "https://courier-api-sandbox.pathao.com";
    protected string $baseUrl = "https://api-hermes.pathao.com";
    /**
     * Get Courier Config
     */
    private function getConfig()
    {
        $courier = Courier::whereHas('method', fn($q) => $q->where('slug', 'pathao'))->first();
        if (!$courier) throw ApiException::serverError('Pathao settings missing.');
        return is_string($courier->method_details)
            ? json_decode($courier->method_details, true)
            : $courier->method_details;
    }

    /**
     * Generate Access Token
     */
    public function getToken(): string
    {
        try {
            $config = $this->getConfig();
            $response = Http::post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
                'client_id'     => $config['client_id'] ?? '',
                'client_secret' => $config['client_secret'] ?? '',
                'username'      => $config['username'] ?? '',
                'password'      => $config['password'] ?? '',
                'grant_type'    => 'password',
            ]);

            if ($response->successful()) return $response->json('access_token');

            throw new \Exception($response->json('message') ?? 'Authentication Failed');
        } catch (\Exception $e) {
            Log::error("Pathao Token Error: {$e->getMessage()}");
            throw ApiException::serverError("Pathao Auth: {$e->getMessage()}");
        }
    }

    /**
     * Create Order on Pathao
     */


    public function createOrder(array $data): array
    {
        $order = Order::with(['customer', 'orderDetails.product'])->find($data['order_id']);

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        // ── Already assigned check ────────────────────────
        if (!empty($order->courier_info) && isset($order->courier_info['consignment_id'])) {
            return [
                'success' => false,
                'message' => "Order already assigned to Pathao. Consignment ID: " . $order->courier_info['consignment_id'],
                'data'    => $order->courier_info
            ];
        }

        // ── Auto-fill from customer ───────────────────────
        $recipientName    = $data['recipient_name']    ?? $order->customer?->name;
        $recipientPhone   = $data['recipient_phone']   ?? $order->customer?->phone;
        $recipientAddress = $data['recipient_address'] ?? $order->customer?->address;

        // ── Validate required recipient fields ────────────
        $errors = [];

        if (empty($recipientName)) {
            $errors['recipient_name'] = ['Recipient name is required. Customer has no name on record.'];
        }

        if (empty($recipientPhone)) {
            $errors['recipient_phone'] = ['Recipient phone is required. Customer has no phone on record.'];
        } elseif (strlen((string) $recipientPhone) !== 11) {
            $errors['recipient_phone'] = ['Phone number must be exactly 11 digits.'];
        }

        if (empty($recipientAddress)) {
            $errors['recipient_address'] = ['Recipient address is required. Customer has no address on record.'];
        } elseif (strlen((string) $recipientAddress) < 10) {
            $errors['recipient_address'] = ['Address is too short. Minimum 10 characters required.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // ── Auto-calculate COD ────────────────────────────
        $amountToCollect = isset($data['amount_to_collect'])
            ? (int) $data['amount_to_collect']
            : (int) max(0, $order->grand_total - $order->payment_amount);

        // ── Auto product title ────────────────────────────
        $productTitle = $data['product_title']
            ?? $order->orderDetails->pluck('product.title')->filter()->join(', ')
            ?? 'Order Items';

        // ── Auto item quantity ────────────────────────────
        $itemQuantity = isset($data['item_quantity'])
            ? (int) $data['item_quantity']
            : (int) max(1, $order->orderDetails->sum('quantity'));

        DB::beginTransaction();
        try {
            $config = $this->getConfig();
            $token  = $this->getToken();

            $payload = [
                "store_id"          => (int) ($config['store_id'] ?? null),
                "merchant_order_id" => (string) ($order->order_no ?? $data['order_id']),
                "recipient_name"    => $recipientName,
                "recipient_phone"   => $recipientPhone,
                "recipient_address" => $recipientAddress,
                "delivery_type"     => (int)    ($data['delivery_type'] ?? 48),
                "item_type"         => (int)    ($data['item_type']     ?? 2),
                "item_quantity"     => $itemQuantity,
                "item_weight"       => (string) ($data['item_weight']   ?? '0.5'),
                "amount_to_collect" => $amountToCollect,
                "item_description"  => $productTitle,
            ];

            // Optional fields — only add if provided
            if (!empty($data['city_id']))                   $payload['city_id']   = (int) $data['city_id'];
            if (!empty($data['zone_id']))                   $payload['zone_id']   = (int) $data['zone_id'];
            if (!empty($data['area_id']))                   $payload['area_id']   = (int) $data['area_id'];
            if (!empty($data['recipient_secondary_phone'])) $payload['recipient_secondary_phone'] = $data['recipient_secondary_phone'];
            if (!empty($data['special_instruction']))       $payload['special_instruction'] = $data['special_instruction'];

            $response = Http::withToken($token)
                ->acceptJson()
                ->post("{$this->baseUrl}/aladdin/api/v1/orders", $payload);

            if ($response->successful()) {
                $resData = $response->json('data');

                $order->update([
                    'courier_info' => [
                        'courier_name'   => 'Pathao',
                        'consignment_id' => $resData['consignment_id'] ?? '',
                        'tracking_code'  => $resData['tracking_code']  ?? '',
                        'status'         => $resData['order_status']   ?? 'Pending',
                        'note'           => $data['special_instruction'] ?? '',
                        'applied_at'     => now()->toDateTimeString(),
                    ]
                ]);

                LogHelper::updated('orders', $order->id, $order->company_id, 'Assigned to Pathao Courier');

                DB::commit();

                Log::info('Pathao order created successfully', ['order_id' => $order->id]);

                return $response->json();
            }

            throw new \Exception($response->json('message') ?? 'Pathao API Order Creation Failed');
        } catch (ValidationException $e) {
            throw $e; // re-throw as-is so controller returns 422
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Pathao Service Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError($e->getMessage());
        }
    }
    /**
     * Create bulk Order on Pathao
     */


    public function createBulkOrder(array $orderIds): array
    {
        // Pre-load order numbers for better error messages
        $orders = Order::whereIn('id', $orderIds)
            ->pluck('order_no', 'id'); // [id => order_no]

        $results = [
            'total'   => count($orderIds),
            'success' => 0,
            'failed'  => 0,
            'details' => [],
        ];

        foreach ($orderIds as $orderId) {
            $orderNo = $orders[$orderId] ?? "#$orderId";

            try {
                $this->createOrder(['order_id' => $orderId]);

                $results['success']++;
                $results['details'][] = [
                    'order_id'  => $orderId,
                    'order_no'  => $orderNo,
                    'success'   => true,
                    'message'   => 'Assigned to Pathao successfully',
                ];

                Log::info("Pathao bulk: Order #{$orderNo} assigned successfully.");
            } catch (ValidationException $e) {
                $errorMsg = collect($e->errors())->flatten()->join(', ');

                $results['failed']++;
                $results['details'][] = [
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'success'  => false,
                    'message'  => 'Validation failed: ' . $errorMsg,
                    'errors'   => $e->errors(),
                ];

                Log::warning("Pathao bulk: Order #{$orderNo} validation failed.", [
                    'errors' => $e->errors(),
                ]);

                $this->logBulkFailure($orderId, "Pathao bulk assign validation failed: {$errorMsg}");
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'success'  => false,
                    'message'  => $e->getMessage(),
                ];

                Log::error("Pathao bulk: Order #{$orderNo} failed. " . $e->getMessage(), [
                    'order_id' => $orderId,
                ]);

                $this->logBulkFailure($orderId, "Pathao bulk assign failed: " . $e->getMessage());
            }
        }

        return $results;
    }

    // ── Helper ────────────────────────────────────────────────
    private function logBulkFailure(int $orderId, string $message): void
    {
        try {
            $order = Order::find($orderId);
            if ($order) {
                LogHelper::updated('orders', $order->id, $order->company_id, $message);
            }
        } catch (\Exception $e) {
            Log::error('Failed to write action log: ' . $e->getMessage());
        }
    }


    /**
     * Sync Single Order Status from Pathao
     */
    public function syncStatus(Order $order): array
    {
        try {
            $config = $this->getConfig();
            $token  = $this->getToken();

            $info = $order->courier_info;
            $consignmentId = $info['consignment_id'] ?? null;

            if (!$consignmentId) {
                throw new \Exception("Consignment ID missing.");
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/aladdin/api/v1/orders/{$consignmentId}");

            if ($response->successful()) {
                $resData = $response->json('data');

                $info['status']     = $resData['order_status'] ?? 'Pending';
                $info['updated_at'] = now()->toDateTimeString();

                $order->courier_info = $info;
                $order->save();

                return [
                    'status' => 200,
                    'message' => 'Status updated successfully',
                    'delivery_status' => $info['status'],
                    'order_id' => $order->id
                ];
            }

            throw new \Exception("Pathao API Error: " . $response->status());
        } catch (\Exception $e) {
            Log::error("Pathao Sync Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync Multiple Orders Status (Bulk)
     */
    public function bulkSyncStatus(array $orderIds): array
    {
        $results = [
            'total'   => count($orderIds),
            'success' => 0,
            'failed'  => 0,
            'details' => []
        ];

        $orders = Order::whereIn('id', $orderIds)->get();

        foreach ($orders as $order) {
            try {
                $syncResponse = $this->syncStatus($order);
                $results['success']++;
                $results['details'][] = $syncResponse;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'order_id' => $order->id,
                    'status'   => 500,
                    'message'  => $e->getMessage()
                ];
            }
        }

        return $results;
    }
    /**
     * Get all orders synced with Pathao with filters
     */
    public function getAllPathaoOrders(array $filters = [])
    {
        $query = Order::query()
            ->where('courier_info->courier_name', 'Pathao');

        if (!empty($filters['status'])) {
            $query->where('courier_info->status', $filters['status']);
        }

        if (!empty($filters['consignment_id'])) {
            $query->where('courier_info->consignment_id', $filters['consignment_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }
    /**
     * Get City List
     */
    public function getCities(): array
    {
        try {
            $token = $this->getToken();
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/aladdin/api/v1/city-list");

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
            $errorMessage = $response->json('message') ?? 'Failed to fetch cities from Pathao';
            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error('Pathao City List Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError($e->getMessage());
        }
    }
    /**
     * Get Zone List by City ID
     */
    public function getZones(int $cityId): array
    {
        try {
            $token = $this->getToken();

            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/aladdin/api/v1/cities/{$cityId}/zone-list");

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            $errorMessage = $response->json('message') ?? 'Failed to fetch zones from Pathao';
            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error("Pathao Zones Error: " . $e->getMessage());
            throw ApiException::serverError($e->getMessage());
        }
    }

    /**
     * Get Area List by Zone ID
     */
    public function getAreas(int $zoneId): array
    {
        try {
            $token = $this->getToken();

            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/aladdin/api/v1/zones/{$zoneId}/area-list");

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            $errorMessage = $response->json('message') ?? 'Failed to fetch areas from Pathao';
            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error("Pathao Areas Error: " . $e->getMessage());
            throw ApiException::serverError($e->getMessage());
        }
    }
}

<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Support\Facades\{Http,Log,DB};

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
        $order = Order::find($data['order_id']);

        if (!$order) {
            throw ApiException::notFound('Order');
        }
        if (!empty($order->courier_info) && isset($order->courier_info['consignment_id'])) {
            return [
                'success' => false,
                'message' => "Order already assigned to Pathao. Consignment ID: " . $order->courier_info['consignment_id'],
                'data'    => $order->courier_info
            ];
        }
        DB::beginTransaction();
        try {
            $config = $this->getConfig();
            $token  = $this->getToken();

            $uniqueMerchantId = (string) ($order->order_no ?? $data['order_id']);

            $payload = [
                "store_id"            => (int) ($config['store_id'] ?? null),
                "merchant_order_id"   => $uniqueMerchantId,
                "recipient_name"      => $data['recipient_name'],
                "recipient_phone"     => $data['recipient_phone'],
                "recipient_address"   => $data['recipient_address'],
                "delivery_type"       => (int) $data['delivery_type'],
                "item_type"           => (int) $data['item_type'],
                "item_quantity"       => (int) $data['item_quantity'],
                "item_weight"         => (string) $data['item_weight'],
                "amount_to_collect"   => (int) $data['amount_to_collect'],
                "item_description"    => $data['product_title'], // Product Title goes here
            ];
            // dd($payload);

            $response = Http::withToken($token)
                ->acceptJson()
                ->post("{$this->baseUrl}/aladdin/api/v1/orders", $payload);

                // dd($response->body());
            if ($response->successful()) {
                $resData = $response->json('data');

                $order->update([
                    'courier_info' => [
                        'courier_name'   => 'Pathao',
                        'consignment_id' => $resData['consignment_id'] ?? '',
                        'tracking_code'  => $resData['tracking_code'] ?? '',
                        'status'         => $resData['order_status'] ?? 'Pending',
                        'applied_at'     => now()->toDateTimeString(),
                    ]
                ]);

                LogHelper::updated('order', $order->id, $order->company_id, 'Assigned to Pathao Courier');

                DB::commit();

                Log::info('Pathao order created successfully', ['order_id' => $order->id]);

                return $response->json();
            }

            throw new \Exception($response->json('message') ?? 'Pathao API Order Creation Failed');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Pathao Service Error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError($e->getMessage());
        }
    }
    /**
     * Create bulk Order on Pathao
     */
    public function createBulkOrder(array $ordersData): array
    {
        $results = [];

        foreach ($ordersData as $orderItem) {
            try {
                $results[] = $this->createOrder($orderItem);
            } catch (\Exception $e) {
                Log::error("Order ID {$orderItem['order_id']} failed in bulk: " . $e->getMessage());
            }
        }

        return $results;
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

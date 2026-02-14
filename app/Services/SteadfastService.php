<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Http;

class SteadfastService
{
    //single order and key from database
    public function sendToSteadfast(Order $order, array $validated)
    {
        $courier = Courier::whereHas('method', function ($q) {
            $q->where('slug', 'steadfast');
        })->first();

        // dd($courier);

        if (!$courier) {
            throw ApiException::serverError('Steadfast settings not found for your company.');
        }

        $config = $courier->method_details;
        $payload = [
            'invoice'           => $order->order_no,
            'recipient_name'    => $order->customer->name ?? 'Customer',
            'recipient_phone'   => $validated['recipient_phone'] ?? $order->customer->phone,
            'recipient_address' => $order->customer->address ?? 'N/A',
            'cod_amount'        => $validated['cod_amount'] ?? $order->grand_total,
            'delivery_type'     => $validated['delivery_type'] ?? 0,
            'note'              => $order->note,
        ];

        $response = Http::withHeaders([
            'Api-Key'      => $config['api_key'],
            'Secret-Key'   => $config['secret_key'],
            'Content-Type' => 'application/json'
        ])->post('https://portal.packzy.com/api/v1/create_order', $payload);

        if ($response->successful() && $response->json('status') == 200) {
            $res = $response->json('consignment');

            $order->update([
                'courier_info' => [
                    'courier_name'   => 'Steadfast',
                    'consignment_id' => $res['consignment_id'],
                    'tracking_code'  => $res['tracking_code'],
                    'status'         => $res['status'],
                    'note'           => $res['note'],
                    'applied_at'     => now()->toDateTimeString(),
                ]
            ]);

            return [
                'status'      => 200,
                'message'     => "Consignment has been created successfully.",
                'consignment' => $res
            ];
        }
        Log::error('Steadfast API Error', [
            'http_status' => $response->status(),
            'response'    => $response->body(),
        ]);
        throw ApiException::serverError($response->json('message') ?? 'Steadfast booking failed.');
    }
    //multiple order
    public function bulkSendToSteadfast(array $orderIds)
    {
        $courier = Courier::whereHas('method', function ($q) {
            $q->where('slug', 'steadfast');
        })->first();

        if (!$courier) {
            throw ApiException::serverError('Steadfast settings not found.');
        }

        $orders = Order::whereIn('id', $orderIds)->with('customer')->get();
        $bulkData = [];

        foreach ($orders as $order) {
            $bulkData[] = [
                'invoice'           => (string) $order->id,
                'recipient_name'    => $order->customer->name ?? 'Customer',
                'recipient_phone'   => $order->customer->phone,
                'recipient_address' => $order->customer->address ?? 'N/A',
                'cod_amount'        => (int) $order->grand_total,
                'note'              => $order->note ?? '',
            ];
        }

        $response = Http::withHeaders([
            'Api-Key'      => $courier->method_details['api_key'],
            'Secret-Key'   => $courier->method_details['secret_key'],
            'Content-Type' => 'application/json'
        ])->post('https://portal.packzy.com/api/v1/create_order/bulk-order', [
            'data' => json_encode($bulkData)
        ]);

        if ($response->successful()) {
            $apiResponse = $response->json();

            $items = $apiResponse['data'] ?? [];

            foreach ($items as $res) {
                if (isset($res['status']) && $res['status'] === 'success') {

                    $order = Order::find($res['invoice']);

                    if ($order) {
                        $order->update([
                            'courier_info' => [
                                'courier_name'   => 'Steadfast',
                                'consignment_id' => $res['consignment_id'] ?? '',
                                'tracking_code'  => $res['tracking_code'] ?? '',
                                'status'         => $res['status'] ?? '',
                                'note'           => $res['note'] ?? '',
                                'applied_at'     => now()->toDateTimeString(),
                            ]
                        ]);
                    }
                }
            }
            return $apiResponse;
        }
        Log::error('Steadfast API Error', [
            'http_status' => $response->status(),
            'response'    => $response->body(),
        ]);
        throw ApiException::serverError('Steadfast Bulk Booking Failed');
    }
    /**
     * Single Order Status Sync
     */
    public function syncStatus(Order $order): array
    {
        $courier = Courier::whereHas('method', fn($q) => $q->where('slug', 'steadfast'))->first();

        if (!$courier) {
            throw new \Exception('Steadfast settings not found.');
        }

        $config = $courier->method_details;

        $info = $info   = $order->courier_info;

        $consignmentId = $info['consignment_id'] ?? null;

        if (!$consignmentId) {
            throw new \Exception("Tracking code missing for Order #{$order->order_no}");
        }

        $url = "https://portal.packzy.com/api/v1/status_by_cid/" . $consignmentId;

        $response = Http::withHeaders([
            'Api-Key'    => $config['api_key'],
            'Secret-Key' => $config['secret_key'],
            'Accept'     => 'application/json'
        ])->get($url);

        // ($response);
        // print($response);

        if ($response->status() === 404) {
            return [
                'status' => 404,
                'message' => 'Order not found in Steadfast yet.',
                'delivery_status' => $info['status'] ?? 'pending'
            ];
        }

        if ($response->successful()) {
            $res = $response->json();

            if (isset($res['status']) && $res['status'] == 200) {
                $info['status'] = $res['delivery_status'];
                $info['updated_at'] = now()->toDateTimeString();

                $order->update(['courier_info' => $info]);

                return [
                    'status' => 200,
                    'message' => 'Status updated',
                    'delivery_status' => $res['delivery_status']
                ];
            }
        }

        throw new \Exception("API Error: " . $response->status());
    }

    public function bulkSyncStatus(array $orderIds): array
    {
        $orders = Order::whereIn('id', $orderIds)->get();
        $results = ['total' => count($orderIds), 'success' => 0, 'failed' => 0, 'details' => []];

        foreach ($orders as $order) {
            try {
                $res = $this->syncStatus($order);

                if (isset($res['status']) && $res['status'] == 200) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['details'][] = "Order #{$order->order_no}: " . ($res['message'] ?? 'Unknown issue');
                }
                $results['order_details'][] = [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'consignment_id' => $order->courier_info['consignment_id'] ?? 'N/A',
                    'current_status' => $order->courier_info['status'] ?? 'pending',
                    'api_message' => $res['message'] ?? 'Sync attempted'
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = "Order #{$order->order_no}: Exception - " . $e->getMessage();
            }
        }
        return $results;
    }
    //get all
    public function getAllSteadfastOrders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Order::query()
                ->where('courier_info->courier_name', 'Steadfast');

            if (!empty($filters['status'])) {
                $query->where('courier_info->status', $filters['status']);
            }

            if (!empty($filters['order_no'])) {
                $query->where('order_no', 'like', "%{$filters['order_no']}%");
            }
            if (!empty($filters['consignment_id'])) {
                $query->where('courier_info->consignment_id', $filters['consignment_id']);
            }

            $sortBy = $filters['sort_by'] ?? 'id';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching Steadfast orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch courier orders');
        }
    }
    //this is fixed steadfast key
    // public function sendToSteadfast(Order $order, array $validated)
    // {
    //     $apiKey = config('services.api_key');
    //     $secretKey = config('services.secret_key');
    //     $baseUrl = config('services.base_url');
    //     // dd(config('services.api_key'));
    //     if (!$apiKey || !$secretKey) {
    //         throw ApiException::serverError('Steadfast configurations are missing in system config.');
    //     }

    //     $payload = [
    //     'invoice'           => $order->order_no,
    //     'recipient_name'    => $order->customer->name ?? 'Customer',
    //     'recipient_phone'   => $validated['recipient_phone'] ?? $order->customer->phone,
    //     'recipient_address' => $order->customer->address ?? 'N/A',
    //     'cod_amount'        => $validated['cod_amount'] ?? $order->grand_total,
    //     'delivery_type'     => $validated['delivery_type'] ?? 0,
    //     'note'              => $order->note,
    // ];

    // $response = Http::withHeaders([
    //     'Api-Key'      => $apiKey,
    //     'Secret-Key'   => $secretKey,
    //     'Content-Type' => 'application/json'
    // ])->post($baseUrl . '/create_order', $payload);

    //     if ($response->successful() && $response->json('status') == 200) {
    //         $res = $response->json('consignment');

    //         $order->update([
    //             'courier_info' => [
    //                 'courier_name'   => 'Steadfast',
    //                 'consignment_id' => $res['consignment_id'],
    //                 'tracking_code'  => $res['tracking_code'],
    //                 'status'         => $res['status'],
    //                 'note'           => $res['note'],
    //                 'applied_at'     => now()->toDateTimeString(),
    //             ]
    //         ]);

    //         return [
    //         'status'      => 200,
    //         'message'     => "Consignment has been created successfully.",
    //         'consignment' => $res
    //     ];
    //     }

    //     throw ApiException::serverError($response->json('message') ?? 'Steadfast booking failed.');
    // }

}

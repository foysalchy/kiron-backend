<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Courier;
use App\Models\Order;
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

        throw ApiException::serverError('Steadfast Bulk Booking Failed');
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

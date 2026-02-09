<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SteadfastOrderRequest;
use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Http\Request;

class SteadfastOrderController extends Controller
{
    public function __construct(protected SteadfastService $service) {}

    public function store(SteadfastOrderRequest $request)
    {
        // dd($request);
        $order = Order::findOrFail($request->order_id);

        if (isset($order->courier_info['tracking_code'])) {
            return ResponseHelper::error('Order already booked with Steadfast.', 400);
        }

        $result = $this->service->sendToSteadfast($order, $request->validated());

        return ResponseHelper::success($result, 'Order shipped successfully.');
    }
    public function bulkStore(Request $request)
    {
        // dd($request);
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);
        $results = $this->service->bulkSendToSteadfast($request->order_ids);

        return ResponseHelper::success($results, 'Bulk orders processed.');
    }
}

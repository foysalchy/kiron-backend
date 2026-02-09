<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SteadfastOrderRequest;
use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SteadfastOrderController extends Controller
{
    public function __construct(protected SteadfastService $service) {}

    public function store(SteadfastOrderRequest $request): JsonResponse
    {
        // dd($request);
        $order = Order::findOrFail($request->order_id);

        if (isset($order->courier_info['tracking_code'])) {
            return ResponseHelper::error('Order already booked with Steadfast.', 400);
        }

        $result = $this->service->sendToSteadfast($order, $request->validated());

        return ResponseHelper::success($result, 'Order shipped successfully.');
    }
    public function bulkStore(Request $request): JsonResponse
    {
        // dd($request);
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);
        $results = $this->service->bulkSendToSteadfast($request->order_ids);

        return ResponseHelper::success($results, 'Bulk orders processed.');
    }
    //update single status
    public function updateStatus($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $result = $this->service->syncStatus($order);
        return ResponseHelper::success($result, 'Order status updated successfully from Steadfast...');
    }
    //update multiple status
    public function updateBulkStatus(Request $request): JsonResponse
    {
       $request->validate(['order_ids' => 'required|array']);

        $data = $this->service->bulkSyncStatus($request->order_ids);
        return response()->json(['status' => 200, 'message' => 'Bulk sync completed', 'data' => $data]);
    }
    //get all data
    public function index(): JsonResponse
    {
        try {
            $result = $this->service->syncAllPendingOrders();
            return ResponseHelper::success($result, 'All pending orders synced with Steadfast.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}

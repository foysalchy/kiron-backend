<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PathaoBulkRequest;
use App\Http\Requests\PathaoRequest;
use App\Models\Order;
use App\Services\PathaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PathaoController extends Controller
{
    public function __construct(protected PathaoService $pathaoService)
    {
    }
    public function testPathaoToken()
    {
        $token = $this->pathaoService->getToken();

        return ResponseHelper::success($token,'Token Created Successfully...');
    }
    public function store(PathaoRequest $request): JsonResponse
    {
        $result = $this->pathaoService->createOrder($request->validated());

        return ResponseHelper::success($result,'Order Created Successfully on Pathao...');
    }
    public function bulkStore(PathaoBulkRequest $request): JsonResponse
    {
        $result = $this->pathaoService->createBulkOrder($request->validated()['orders']);

        return ResponseHelper::success($result,'Bulk Order Created Successfully on Pathao...');
    }
    /**
     * Sync Single Status
     */
    public function updateStatus($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $result = $this->pathaoService->syncStatus($order);
        return ResponseHelper::success($result, 'Order status updated from Pathao.');
    }
    /**
     * Sync Multiple Status
     */
    public function updateBulkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $data = $this->pathaoService->bulkSyncStatus($request->order_ids);

        return ResponseHelper::success($data, 'Bulk order status updated successfully from Pathao.');
    }
    /**
     * Display a listing of Pathao orders.
     */
    public function index(Request $request): JsonResponse
    {
            $filters = $request->only([
                'status',
                'consignment_id',
                'per_page'
            ]);

            $result = $this->pathaoService->getAllPathaoOrders($filters);

            return ResponseHelper::success($result, 'Pathao orders retrieved successfully.');
    }
}

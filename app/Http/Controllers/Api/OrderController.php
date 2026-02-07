<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UpdateOrderRequest;
use App\Services\FrontendOrderService;
use App\Services\OrderService;
use Illuminate\Http\{JsonResponse, Request};

class OrderController extends Controller
{


    public function __construct(
        protected FrontendOrderService $frontendOrderService,
        protected OrderService $orderService,
    ) {}

    /**
     * Get all orders with filters
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'order_no' => $request->input('order_no'),
                'product_name' => $request->input('product_name'),
                'product_sku' => $request->input('product_sku'),
                'order_date' => $request->input('order_date'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'type' => $request->input('type'),
                'warehouse_id' => $request->input('warehouse_id'),
                'search' => $request->input('search'),
                'per_page' => $request->input('per_page', 20),
            ];

            $orders = $this->frontendOrderService->getOrders($filters);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single order by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $order = $this->frontendOrderService->getOrderById($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {

        $request->validate([
            'payment_status' => 'nullable',
            'order_status' => 'nullable',
        ]);

        $data = $this->frontendOrderService->updateStatus($id, (int) $request->payment_status, $request->order_status);

        return ResponseHelper::success($data, 'Status Changed Successfully');
    }
    public function changeStatus(Request $request, $id): JsonResponse
    {

        $request->validate([
            'status' => 'required',
        ]);

        $data = $this->orderService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Status Changed Successfully');
    }
    /**
     * Update order 
     */
    public function update(UpdateOrderRequest $request, $id)
    {


        $data = $this->orderService->updateOrder($id, $request->validated(), $request->type);

        return ResponseHelper::success($data, 'Status Changed Successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class POSOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Get all POS orders
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'type' => 'pos',
            'warehouse_id' => $request->query('warehouse_id'),
            'customer_id' => $request->query('customer_id'),
            'status' => $request->query('status'),
            'payment_status' => $request->query('payment_status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'order_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->orderService->getAllOrders($filters, true);

        return ResponseHelper::success($data, 'POS orders retrieved successfully');
    }

    /**
     * Create new POS order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $this->orderService->createPOSOrder($request->validated());

        return ResponseHelper::success($data, 'POS order created successfully', 201);
    }

    /**
     * Get single POS order
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->orderService->getOrderById($id,'pos');

        return ResponseHelper::success($data, 'POS order retrieved successfully');
    }
    public function heldOrders(int $warehouseId): JsonResponse
    {
        $data = $this->orderService->getHoldOrderList($warehouseId,'pos');

        return ResponseHelper::success($data, 'POS order retrieved successfully');
    }

    /**
     * Cancel order
     */
    public function cancel(int $id): JsonResponse
    {
        $data = $this->orderService->cancelOrder($id,'pos');

        return ResponseHelper::success($data, 'POS order cancelled successfully');
    }

    /**
     * Complete order
     */
    public function complete(int $id): JsonResponse
    {
        $data = $this->orderService->completeOrder($id,'pos');

        return ResponseHelper::success($data, 'POS order completed successfully');
    }

    /**
     * Hold order
     */
    public function hold(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'hold_ref' => 'nullable|string|max:500',
        ]);

        $data = $this->orderService->holdOrder($id, $request->hold_reason,'pos');

        return ResponseHelper::success($data, 'POS order put on hold successfully');
    }

    /**
     * Resume held order
     */
    public function resume(int $id): JsonResponse
    {
        $data = $this->orderService->resumeOrder($id,'pos');

        return ResponseHelper::success($data, 'POS order resumed successfully');
    }
}
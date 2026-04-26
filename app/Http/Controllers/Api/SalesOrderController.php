<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Helpers\ResponseHelper;
use App\Models\Order;
use Illuminate\Http\{JsonResponse, Request};

class SalesOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Get all sales orders
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'type' => 'sales',
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

        return ResponseHelper::success($data, 'Sales orders retrieved successfully');
    }

    /**
     * Create new sales order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $this->orderService->createSalesOrder($request->validated());

        return ResponseHelper::success($data, 'Sales order created successfully', 201);
    }

    /**
     * Get single sales order
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->orderService->getOrderById($id, 'sales');

        return ResponseHelper::success($data, 'Sales order retrieved successfully');
    }

    /**
     * Cancel order
     */
    public function cancel(int $id): JsonResponse
    {
        $data = $this->orderService->cancelOrder($id, 'sales');

        return ResponseHelper::success($data, 'Sales order cancelled successfully');
    }

    /**
     * Complete order
     */
    public function complete(int $id): JsonResponse
    {
        $data = $this->orderService->completeOrder($id, 'sales');

        return ResponseHelper::success($data, 'Sales order completed successfully');
    }
    public function assignUsers(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $data = $this->orderService->assignUsers($id, $validated['user_ids']);

        return ResponseHelper::success($data, 'Users assigned successfully');
    }
}

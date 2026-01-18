<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePosOrderRequest;
use App\Services\PosOrderService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class PosOrderController extends Controller
{
    public function __construct(
        protected PosOrderService $posOrderService
    ) {}

    /**
     * Get all POS orders
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'customer_id' => $request->query('customer_id'),
            'is_walk_in' => $request->query('is_walk_in'),
            'status' => $request->query('status'),
            'payment_status' => $request->query('payment_status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'order_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->posOrderService->getAllPosOrders($filters, true);

        return ResponseHelper::success($data, 'POS orders retrieved successfully');
    }

    /**
     * Create new POS order
     */
    public function store(StorePosOrderRequest $request): JsonResponse
    {
        $data = $this->posOrderService->createPosOrder($request->validated());

        return ResponseHelper::success($data, 'POS order created successfully', 201);
    }

    /**
     * Get single POS order
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->posOrderService->getPosOrderById($id);

        return ResponseHelper::success($data, 'POS order retrieved successfully');
    }

    /**
     * Cancel order
     */
    public function cancel(int $id): JsonResponse
    {
        $data = $this->posOrderService->cancelOrder($id);

        return ResponseHelper::success($data, 'POS order cancelled successfully');
    }

    /**
     * Complete order
     */
    public function complete(int $id): JsonResponse
    {
        $data = $this->posOrderService->completeOrder($id);

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

        $data = $this->posOrderService->holdOrder($id, $request->hold_ref);

        return ResponseHelper::success($data, 'POS order put on hold successfully');
    }

    /**
     * Resume held order
     */
    public function resume(int $id): JsonResponse
    {
        $data = $this->posOrderService->resumeOrder($id);

        return ResponseHelper::success($data, 'POS order resumed successfully');
    }

    /**
     * Get all held orders
     */
    public function heldOrders(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $data = $this->posOrderService->getHeldOrders($request->warehouse_id);

        return ResponseHelper::success($data, 'Held orders retrieved successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreOrderReturnRequest, UpdateOrderReturnRequest};
use App\Services\OrderReturnService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class OrderReturnController extends Controller
{
    public function __construct(
        protected OrderReturnService $orderReturnService
    ) {}

    /**
     * Get all order returns
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'customer_id' => $request->query('customer_id'),
            'order_id' => $request->query('order_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'return_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->orderReturnService->getAllOrderReturns($filters, true);

        return ResponseHelper::success($data, 'Order returns retrieved successfully');
    }

    /**
     * Create new order return
     */
    public function store(StoreOrderReturnRequest $request): JsonResponse
    {

        $data = $this->orderReturnService->createOrderReturn($request->validated());

        return ResponseHelper::success($data, 'Order return created successfully', 201);
    }

    /**
     * Get single order return
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->orderReturnService->getOrderReturnById($id);

        return ResponseHelper::success($data, 'Order return retrieved successfully');
    }

    /**
     * Update order return
     */
    public function update(UpdateOrderReturnRequest $request, int $id): JsonResponse
    {
        $data = $this->orderReturnService->updateOrderReturn($id, $request->validated());

        return ResponseHelper::success($data, 'Order return updated successfully');
    }

    /**
     * Delete order return
     */
    public function destroy(int $id): JsonResponse
    {
        $this->orderReturnService->deleteOrderReturn($id);

        return ResponseHelper::success(null, 'Order return deleted successfully');
    }

    /**
     * Add payment to return
     */
    public function addPayment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,bank,mobile_banking,cheque',
            'reference_no' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = $this->orderReturnService->addPayment($id, $request->only([
            'amount',
            'payment_method',
            'reference_no',
            'note'
        ]));

        return ResponseHelper::success($data, 'Payment added successfully');
    }
    /**
     * Add payment to return
     */
    public function modifyRefund(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',

        ]);

        $data = $this->orderReturnService->modifyRefund($id, $request->only([
            'amount'
        ]));

        return ResponseHelper::success($data, 'Refund amount modify successfully');
    }

    /**
     * Change status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Status::Pending->value,      // 2
                    Status::Waiting->value,  // 16
                    Status::Completed->value,  // 16
                    Status::Cancelled->value,  // 10
                    Status::NotCleared->value,  // 18
                    Status::Cleared->value,  // 17
                ]),
            ],
        ]);

        $data = $this->orderReturnService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Order return status updated successfully');
    }

    /**
     * Restore purchase return
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->orderReturnService->restoreOrderReturn($id);

        return ResponseHelper::success($data, 'Order return restored successfully');
    }

    /**
     * Force delete purchase return
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->orderReturnService->forceDeleteOrderReturn($id);

        return ResponseHelper::success(null, 'Order return permanently deleted successfully');
    }
}

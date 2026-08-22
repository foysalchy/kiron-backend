<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Helpers\ResponseHelper;
use App\Http\Requests\AddPaymentRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\CourierCheckHistory;
use App\Models\Order;
use App\Services\FrontendOrderService;
use App\Services\OrderService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $statusCounts = $this->frontendOrderService->getOrderCountsByStatus();

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'status_counts' => $statusCounts,
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
    public function show(int $id): JsonResponse
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
    public function customerOrders(int $customerId): JsonResponse
    {
        try {
            $order = $this->frontendOrderService->getCustomerOrder($customerId);

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
    public function getEditOrder(int $id): JsonResponse
    {
        $data = $this->frontendOrderService->getEditOrder($id);

        return ResponseHelper::success($data, 'POS order retrieved successfully');
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
    public function deleteOrder($id)
    {

        $this->orderService->deleteOrder($id);

        return ResponseHelper::success(null, 'Order Deleted.');
    }
    /**
     * Update order 
     */
    public function update(UpdateOrderRequest $request, $id)
    {


        $data = $this->orderService->updateOrder($id, $request->validated(), $request->type);

        return ResponseHelper::success($data, 'Status Changed Successfully');
    }
    public function updateShiping(Request $request, $id)
    {
        $validated = $request->validate([
            'shipping_address' => 'nullable|array',
            'shipping_address.name' => 'nullable|string',
            'shipping_address.phone' => 'nullable|string',
            'shipping_address.address' => 'nullable|string',
            'shipping_address.division' => 'nullable|string',
            'shipping_address.district' => 'nullable|string',
            'shipping_address.thana' => 'nullable|string',
        ]);

        $data = $this->orderService->updateShiping($id, $validated, $request->type);

        return ResponseHelper::success($data, 'Status Changed Successfully');
    }
    public function addPayment(AddPaymentRequest $request, $id)
    {


        $data = $this->orderService->addPayment($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully',

        ]);
    }
    public function getSelectListOrder(Request $request)
    {
        $q = $request->query('q');

        $data = $this->orderService->getSelectListOrder($q);

        return ResponseHelper::success($data, 'Order list retrieved');
    }


    public function orderProducts(int $orderId)
    {
        $data = $this->orderService->orderProducts($orderId);

        return ResponseHelper::success($data, 'Order product list retrive');
    }
    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:orders,id',
            'status' => 'required'
        ]);

        try {
            DB::beginTransaction();

            Order::whereIn('id', $validated['ids'])
                ->update(['status' => $validated['status']]);

            foreach ($validated['ids'] as $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    app(\App\Services\StatusSyncService::class)->syncOrderStatus($order);
                }
                LogHelper::statusChanged('orders', $orderId, auth()->user()->company_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'updated_count' => count($validated['ids']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating status',
            ], 500);
        }
    }
    public function checkCourier(Request $request)
    {
        $phone = $request->query('phone');
        $forceRefresh = $request->boolean('refresh');

        if (!$phone) {
            throw ApiException::badRequest('Phone number is required.');
        }

        $companyId = auth()->user()->company_id ?? null;

        $existing = CourierCheckHistory::where('company_id', $companyId)
            ->where('phone', $phone)
            ->first();

        // Normal load (no refresh) -> serve from cache if exists
        if (!$forceRefresh) {
            if ($existing) {
                return response()->json([
                    'courierData' => $existing->response_data,
                    'source' => 'cache',
                    'checked_at' => $existing->checked_at,
                    'next_allowed_at' => $existing->checked_at->addHours(2),

                ]);
            }
            // no cache, fresh fetch needed even without explicit refresh
        }

        // Refresh requested -> check rate limit
        if ($forceRefresh && $existing && $existing->checked_at) {
            $nextAllowedAt = $existing->checked_at->addHours(2);

            if (now()->lessThan($nextAllowedAt)) {
                return response()->json([
                    'message' => 'Refresh limit reached. Please try again later.',
                    'courierData' => $existing->response_data,
                    'source' => 'cache',
                    'checked_at' => $existing->checked_at,
                    'next_allowed_at' => $nextAllowedAt,
                    'retry_after_seconds' => now()->diffInSeconds($nextAllowedAt),
                ], 429);
            }
        }

        // Call external API
        try {
            $response = Http::withToken(config('services.bdcourier.key'))
                ->post("https://bdcourier.com/api/courier-check?phone={$phone}");

            if ($response->status() === 422) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response->json('errors.phone.0')
                        ?? $response->json('message')
                        ?? 'Please enter a valid phone number.',
                ], 422);
            }

            if (!$response->successful()) {
                Log::error('Courier API failed: ' . $response->status() . ' - ' . $response->body());
                throw ApiException::serverError('Courier API request failed.');
            }

            $data = $response->json('courierData');

            $record = CourierCheckHistory::updateOrCreate(
                ['company_id' => $companyId, 'phone' => $phone],
                ['response_data' => $data, 'checked_at' => now()]
            );

            return response()->json([
                'courierData' => $data,
                'source' => 'live',
                'checked_at' => $record->checked_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Courier check failed: ' . $e->getMessage());
            throw ApiException::serverError('Unable to fetch courier history.');
        }
    }
}

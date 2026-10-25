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
                'source' => $request->input('source'),
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
                'message' => 'Error: ' . $e->getMessage() . ' Line: ' . $e->getLine() . ' File: ' . $e->getFile(),
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

        return ResponseHelper::success($data, 'Shipping address updated successfully');
    }

    public function updateShipping(Request $request, $id)
    {
        return $this->updateShiping($request, $id);
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
    public function payOrderFromWallet(Request $request, int $customerId)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'amount'   => 'required|numeric|min:0.01',
        ]);

        $data = $this->orderService->payOrderFromWallet(
            $customerId,
            $validated['order_id'],
            (float) $validated['amount']
        );

        return ResponseHelper::success($data, 'Order paid from wallet balance successfully');
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

    /**
     * Get order product list slip grouped by category (MegaCategory)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pickingSlip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer',
            'with_suppliers' => 'nullable|boolean',
            'with_prices' => 'nullable|boolean',
            'group_by' => 'nullable|string|in:category,order',
        ]);

        $orderIds = $validated['order_ids'];
        $withSuppliers = $request->boolean('with_suppliers', false);
        $withPrices = $request->boolean('with_prices', false);
        $groupBy = $request->input('group_by', 'category');

        // If order-wise requested
        if ($groupBy === 'order') {
            $orders = \App\Models\Order::whereIn('id', $orderIds)
                ->with([
                    'customer:id,name,phone',
                    'orderDetails.product' => function ($query) {
                        $query->select('id', 'title', 'thumbnail', 'thumbnail_95');
                    },
                    'orderDetails.variation' => function ($query) {
                        $query->select('id', 'product_id', 'regular_price', 'image');
                    },
                    'orderDetails.variation.attributes.attributeGroup',
                    'orderDetails.variation.attributes.attributeValue',
                ])
                ->get();

            if ($orders->isEmpty()) {
                return ResponseHelper::success(['orders' => [], 'total_products' => 0], 'No orders found.');
            }

            $orderList = [];
            $totalProductsCount = 0;

            foreach ($orders as $order) {
                $shipping = $order->shipping_address ?? [];
                $customerName = $order->customer?->name ?? ($shipping['name'] ?? null);
                $customerPhone = $order->customer?->phone ?? ($shipping['phone'] ?? null);

                $items = [];
                foreach ($order->orderDetails as $detail) {
                    $product = $detail->product;
                    if (!$product) {
                        continue;
                    }

                    $variationTitle = '';
                    if ($detail->variation && $detail->variation->attributes) {
                        $attrs = $detail->variation->attributes->map(function ($attr) {
                            $group = $attr->attributeGroup->name ?? '';
                            $val = $attr->attributeValue->name ?? '';
                            return trim("{$group}: {$val}");
                        })->filter()->implode(', ');
                        if (!empty($attrs)) {
                            $variationTitle = " ({$attrs})";
                        }
                    }

                    $thumbnail = null;
                    if ($detail->variation && !empty($detail->variation->image)) {
                        $thumbnail = $detail->variation->image_url;
                    }
                    if (!$thumbnail) {
                        $thumbnail = $product->thumbnail_95_url ?: ($product->thumbnail_url ?: null);
                    }

                    $items[] = [
                        'product_id' => $product->id,
                        'variation_id' => $detail->variation_id,
                        'title' => $product->title . $variationTitle,
                        'thumbnail_95' => $thumbnail,
                        'quantity' => (int) $detail->quantity,
                    ];
                    $totalProductsCount += (int) $detail->quantity;
                }

                $orderList[] = [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no ?: (string) $order->id,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'items' => $items,
                ];
            }

            return ResponseHelper::success([
                'total_products' => $totalProductsCount,
                'orders' => $orderList,
                'group_by' => 'order',
            ], 'Order-wise product picking slip generated successfully.');
        }

        // Fetch order details for the given orders
        $orderDetails = \App\Models\OrderDetail::whereIn('order_id', $orderIds)
            ->with([
                'product' => function ($query) use ($withPrices) {
                    $cols = ['id', 'title', 'thumbnail', 'thumbnail_95', 'mega_category_ids'];
                    if ($withPrices) {
                        $cols[] = 'purchase_price';
                    }
                    $query->select($cols);
                },
                'variation' => function ($query) use ($withPrices) {
                    $cols = ['id', 'product_id', 'regular_price', 'image'];
                    if ($withPrices) {
                        $cols[] = 'purchase_price';
                    }
                    $query->select($cols);
                },
                'variation.attributes.attributeGroup',
                'variation.attributes.attributeValue',
            ])
            ->get();

        if ($orderDetails->isEmpty()) {
            return ResponseHelper::success(['categories' => []], 'No products found for selected orders.');
        }

        // Pre-fetch latest purchase_details.purchase_price ONLY when with_prices is true
        $latestPurchasePrices = [];
        if ($withPrices) {
            $orderProductIds = [];
            foreach ($orderDetails as $detail) {
                if ($detail->product_id) {
                    $orderProductIds[$detail->product_id] = $detail->product_id;
                }
            }

            if (!empty($orderProductIds)) {
                $purchaseDetailQuery = \App\Models\PurchaseDetail::whereIn('purchase_details.product_id', array_values($orderProductIds))
                    ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                    ->whereNull('purchases.deleted_at')
                    ->where('purchases.status', \App\Enums\Status::Completed->value)
                    ->whereNotNull('purchase_details.purchase_price')
                    ->where('purchase_details.purchase_price', '>', 0)
                    ->select(
                        'purchase_details.product_id',
                        'purchase_details.variation_id',
                        'purchase_details.purchase_price',
                        'purchases.purchase_date',
                        'purchases.id as purchase_id'
                    )
                    ->orderBy('purchases.purchase_date', 'desc')
                    ->orderBy('purchases.id', 'desc')
                    ->get();

                foreach ($purchaseDetailQuery as $pd) {
                    $pId = $pd->product_id;
                    $vId = $pd->variation_id ?: 0;
                    $pKey = "{$pId}_{$vId}";

                    if (!isset($latestPurchasePrices[$pKey])) {
                        $latestPurchasePrices[$pKey] = (float) $pd->purchase_price;
                    }
                    $productKey = "{$pId}_0";
                    if (!isset($latestPurchasePrices[$productKey])) {
                        $latestPurchasePrices[$productKey] = (float) $pd->purchase_price;
                    }
                }
            }
        }

        // Aggregate by product and variation: key = productId_variationId
        $aggregatedItems = [];
        $allProductIds = [];

        foreach ($orderDetails as $detail) {
            $product = $detail->product;
            if (!$product) {
                continue;
            }

            $productId = $product->id;
            $variationId = $detail->variation_id ?: 0;
            $key = "{$productId}_{$variationId}";

            $allProductIds[$productId] = $productId;

            // Variation label if available
            $variationTitle = '';
            if ($detail->variation && $detail->variation->attributes) {
                $attrs = $detail->variation->attributes->map(function ($attr) {
                    $group = $attr->attributeGroup->name ?? '';
                    $val = $attr->attributeValue->name ?? '';
                    return trim("{$group}: {$val}");
                })->filter()->implode(', ');
                if (!empty($attrs)) {
                    $variationTitle = " ({$attrs})";
                }
            }

            if (!isset($aggregatedItems[$key])) {
                // Variation image優先, fallback to product thumbnail_95
                $thumbnail = null;
                if ($detail->variation && !empty($detail->variation->image)) {
                    $thumbnail = $detail->variation->image_url;
                }
                if (!$thumbnail) {
                    $thumbnail = $product->thumbnail_95_url ?: ($product->thumbnail_url ?: null);
                }

                $itemData = [
                    'product_id' => $productId,
                    'variation_id' => $detail->variation_id,
                    'title' => $product->title . $variationTitle,
                    'thumbnail_95' => $thumbnail,
                    'quantity' => 0,
                    'mega_category_ids' => $product->mega_category_ids ?? [],
                    'suppliers' => [],
                ];

                if ($withPrices) {
                    // 1st: purchase_details.purchase_price
                    // 2nd: product_variations.purchase_price
                    // 3rd: products.purchase_price
                    // fallback: 0
                    $purchasePrice = 0.0;
                    if (isset($latestPurchasePrices[$key])) {
                        $purchasePrice = $latestPurchasePrices[$key];
                    } elseif ($variationId && isset($latestPurchasePrices["{$productId}_0"])) {
                        $purchasePrice = $latestPurchasePrices["{$productId}_0"];
                    } elseif ($detail->variation?->purchase_price !== null && $detail->variation?->purchase_price !== '' && (float) $detail->variation->purchase_price > 0) {
                        $purchasePrice = (float) $detail->variation->purchase_price;
                    } elseif ($product->purchase_price !== null && $product->purchase_price !== '' && (float) $product->purchase_price > 0) {
                        $purchasePrice = (float) $product->purchase_price;
                    }

                    $itemData['purchase_price'] = $purchasePrice;
                    $itemData['sale_price'] = (float) $detail->unit_price;
                }

                $aggregatedItems[$key] = $itemData;
            }

            $aggregatedItems[$key]['quantity'] += (int) $detail->quantity;
        }

        // Fetch last 3 unique suppliers per product if requested
        if ($withSuppliers && !empty($allProductIds)) {
            $purchaseDetails = \App\Models\PurchaseDetail::whereIn('product_id', array_values($allProductIds))
                ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
                ->join('parties', 'parties.id', '=', 'purchases.supplier_id')
                ->whereNull('purchases.deleted_at')
                ->select(
                    'purchase_details.product_id',
                    'parties.id as supplier_id',
                    'parties.name as supplier_name',
                    'purchases.purchase_date',
                    'purchases.id as purchase_id'
                )
                ->orderBy('purchases.purchase_date', 'desc')
                ->orderBy('purchases.id', 'desc')
                ->get();

            $suppliersByProduct = [];
            foreach ($purchaseDetails as $pd) {
                $pId = $pd->product_id;
                if (!isset($suppliersByProduct[$pId])) {
                    $suppliersByProduct[$pId] = [];
                }
                // Check if supplier already in the list
                $exists = false;
                foreach ($suppliersByProduct[$pId] as $s) {
                    if ($s['id'] == $pd->supplier_id) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists && count($suppliersByProduct[$pId]) < 3) {
                    $suppliersByProduct[$pId][] = [
                        'id' => $pd->supplier_id,
                        'name' => $pd->supplier_name,
                    ];
                }
            }

            foreach ($aggregatedItems as &$item) {
                $pId = $item['product_id'];
                $item['suppliers'] = $suppliersByProduct[$pId] ?? [];
            }
            unset($item);
        }

        // Collect all distinct mega_category_ids to query Category names
        $allCategoryIds = [];
        foreach ($aggregatedItems as $item) {
            if (!empty($item['mega_category_ids']) && is_array($item['mega_category_ids'])) {
                foreach ($item['mega_category_ids'] as $catId) {
                    $allCategoryIds[$catId] = $catId;
                }
            }
        }

        $megaCategories = [];
        if (!empty($allCategoryIds)) {
            $megaCategories = \App\Models\MegaCategory::whereIn('id', array_values($allCategoryIds))
                ->pluck('name', 'id')
                ->toArray();
        }

        // Group aggregated items by MegaCategory
        $grouped = [];
        foreach ($aggregatedItems as $item) {
            $catIds = $item['mega_category_ids'] ?? [];
            $assignedCatName = 'Uncategorized';
            $assignedCatId = 0;

            if (!empty($catIds) && is_array($catIds)) {
                $firstId = $catIds[0];
                if (isset($megaCategories[$firstId])) {
                    $assignedCatName = $megaCategories[$firstId];
                    $assignedCatId = $firstId;
                }
            }

            $catKey = "cat_{$assignedCatId}";
            if (!isset($grouped[$catKey])) {
                $grouped[$catKey] = [
                    'category_id' => $assignedCatId,
                    'category_name' => $assignedCatName,
                    'items' => [],
                ];
            }

            $grouped[$catKey]['items'][] = $item;
        }

        // Sort so that Uncategorized (category_id === 0) is always at the end
        $categoriesList = array_values($grouped);
        usort($categoriesList, function ($a, $b) {
            if ($a['category_id'] === 0) return 1;
            if ($b['category_id'] === 0) return -1;
            return strcasecmp($a['category_name'], $b['category_name']);
        });

        return ResponseHelper::success([
            'total_products' => count($aggregatedItems),
            'categories' => $categoriesList,
        ], 'Product picking slip generated successfully.');
    }

    /**
     * Toggle packed status for courier parcel
     *
     * @param int $id
     * @return JsonResponse
     */
    public function togglePacked(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $courierInfo = $order->courier_info;
        if (!is_array($courierInfo)) {
            $courierInfo = json_decode($courierInfo, true) ?: [];
        }

        $currentPacked = !empty($courierInfo['is_packed']);
        $newPacked = !$currentPacked;
        $courierInfo['is_packed'] = $newPacked;

        $order->courier_info = $courierInfo;
        $order->save();

        return ResponseHelper::success([
            'is_packed' => $newPacked,
        ], $newPacked ? 'Order marked as packed.' : 'Order marked as unpacked.');
    }
}

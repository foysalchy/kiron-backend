<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\{Cart as CartTrack, CustomerPaymentMethod, Order, OrderPayment, Party, Product, ProductReview, ProductStockLedger, ProductVariation, ProductVariationStock, ProductVariationStockLedger, SiteSetting, Warehouse};
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Cache, DB, Hash, Log, Session};

class OrderController extends FrontendController
{
    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }

    public function index()
    {


        if (Cart::count() == 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        $paymentMethods = CustomerPaymentMethod::where('company_id', $this->company_id)->where('status', Status::Active->value)
            ->select(['id', 'company_id', 'name', 'account_number', 'method_details', 'status'])->get();

        $existingDraftId = Session::get('current_draft_order_id');
        $existingDraft   = $existingDraftId
            ? Order::where('id', $existingDraftId)
            ->where('status', Status::Draft->value)
            ->first()
            : null;
        // Auto-create draft for logged in users
        if (auth('customer')->check() && !$existingDraft) {
            $this->createDraftOrder([
                'phone'   => auth('customer')->user()->phone,
                'name'    => auth('customer')->user()->name,
                'address' => auth('customer')->user()->address,
            ]);
        }

        $settings = SiteSetting::where('company_id', $this->company_id)->select(['id', 'company_id', 'inside_charge', 'outside_charge'])->first();
        $defaultInside = $settings->inside_charge ?? 60;
        $cartContent = Cart::content();


        $subtotal = (float) str_replace(',', '', Cart::subtotal());
        $shipping = session()->get('shipping_cost', $defaultInside);
        $shipping_area = session()->get('shipping_area', 'inside');
        $discount = session()->has('coupon') ? session('coupon')['discount_amount'] : 0;
        $total = ($subtotal - $discount) + $shipping;

        $draftOrderId = Session::get('current_draft_order_id');

        // dd($cartContent, $subtotal, $discount, $shipping, $total, $shipping_area, $paymentMethods, $draftOrderId);
        return $this->view('frontend.checkout', compact(
            'cartContent',
            'subtotal',
            'discount',
            'shipping',
            'total',
            'shipping_area',
            'paymentMethods',
            'draftOrderId'
        ));
    }

    public function partialSave(Request $request)
    {
        if (!$request->phone || strlen($request->phone) < 11) {
            return response()->json(['success' => false, 'message' => 'Invalid phone number']);
        }
        $res = $this->createDraftOrder($request->all());
        return response()->json($res);
    }

    private function createDraftOrder($data)
    {
        $cartContent = Cart::content();

        if ($cartContent->isEmpty()) {
            return ['success' => false, 'error' => 'Cart is empty'];
        }
        //  Log::info('Cart content', ['count' => $cartContent->count()]);
        $warehouseInfo = [];
        $warehouseIds = [];

        foreach ($cartContent as $item) {
            // Fluent object থেকে সঠিকভাবে data নাও
            $vId = $item->options->get('variation_id') ?? $item->options->variation_id ?? null;
            $wId = $item->options->get('warehouse_id') ?? $item->options->warehouse_id ?? null;
            $bId = $item->options->get('bin_id') ?? $item->options->bin_id ?? null;

            $wId = $wId ? (int) $wId : null;

            Log::info('Cart item options fixed', [
                'item_id'      => $item->id,
                'variation_id' => $vId,
                'warehouse_id' => $wId,
            ]);

            if ($wId) {
                $warehouseIds[] = $wId;
            }

            $warehouseInfo[] = [
                'product_id'   => $vId ? (ProductVariation::find($vId)?->product_id) : (int) $item->id,
                'variation_id' => $vId,
                'warehouse_id' => $wId,
                'bin_id'       => $bId,
                'quantity'     => (int) $item->qty,
            ];
        }


        try {
            DB::beginTransaction();

            $customer = auth('customer')->check()
                ? auth('customer')->user()
                : Party::updateOrCreate(
                    ['phone' => $data['phone'], 'company_id' => $this->company_id, 'type' => Party::TYPE_CUSTOMER],
                    [
                        'name'     => $data['name'] ?? 'Guest',
                        'password' => Hash::make('12345678'),
                        'status'   => Status::Active->value
                    ]
                );
            // if (!auth('customer')->check()) {
            //     auth('customer')->login($customer);
            // }

            $items = [];
            foreach ($cartContent as $item) {
                $vId = $item->options->get('variation_id') ?? $item->options->variation_id ?? null;
                $wId = $item->options->get('warehouse_id') ?? $item->options->warehouse_id ?? null;
                $bId = $item->options->get('bin_id') ?? $item->options->bin_id ?? null;

                $items[] = [
                    'product_id'   => $vId ? ProductVariation::find($vId)?->product_id : (int) $item->id,
                    'variation_id' => $vId,
                    'quantity'     => (int) $item->qty,
                    'unit_price'   => (float) $item->price,
                    'warehouse_id' => $wId ? (int)$wId : null, // ⚡ নিশ্চিত করুন এখানে $wId ব্যবহার হয়েছে
                    'bin_id'       => $bId,
                ];
            }

            $shippingAddress = [
                'name'    => $data['name']    ?? $customer->name,
                'phone'   => $data['phone']   ?? $customer->phone,
                'email'   => $data['email']   ?? $customer->email ?? null,
                'district' => $data['district'] ?? $customer->district ?? null,
                'address' => $data['address'] ?? $customer->address ?? 'N/A',
            ];

            $oldId    = Session::get('current_draft_order_id');
            $oldOrder = $oldId
                ? Order::where('id', $oldId)->where('status', Status::Draft->value)->first()
                : null;

            if ($oldOrder) {
                // Delete old details and the order itself so it can be cleanly recreated with accurate totals and relations
                $oldOrder->orderDetails()->delete();
                $oldOrder->delete();
            }

            $orderData = [

                'warehouse_info'   => $warehouseInfo, // JSON Column Store
                'customer_id'      => $customer->id,
                'company_id'       => $this->company_id,
                'items'            => $items,
                'status'           => Status::Draft->value,
                'order_date'       => now(),
                'other_charges'    => session()->get('shipping_cost', 60),
                'shipping_address' => $shippingAddress,
            ];
            Log::info('Order data before create', [
                'status' => $orderData['status'],
                'status_draft_value' => Status::Draft->value,
            ]);

            $order = $this->orderService->createSalesOrder($orderData);
            Session::put('current_draft_order_id', $order->id);

            DB::commit();
            return ['success' => true, 'order_id' => $order->id, 'logged_in' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Draft creation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    public function storeOrder(Request $request)
    {
        Log::info('storeOrder called', ['payment_method' => $request->payment_method]);

        $pm = strtolower($request->payment_method ?? '');
        $isCOD = (str_contains($pm, 'cash') || str_contains($pm, 'delivery') || $pm == 'cod');

        $request->validate([
            'name'           => 'required',
            'phone'          => 'required',
            'email'          => 'nullable|email',
            'district'       => 'required|string',
            'address'        => 'required',
            'payment_method' => 'required|string',
            'transaction_id' => $isCOD ? 'nullable' : 'required|string|unique:order_payments,transaction_id',
            'amount'         => $isCOD ? 'nullable' : 'required|numeric',
            'screenshots'    => 'nullable|array|max:3',
            'screenshots.*'  => 'image|max:2048',
        ]);

        // 1. Draft order find & ALWAYS Sync with latest Cart Content
        $draftResult = $this->createDraftOrder($request->all());
        if (!isset($draftResult['success']) || !$draftResult['success']) {
            return back()->with('error', 'Order failed: ' . ($draftResult['error'] ?? 'Unknown error'));
        }

        $orderId = Session::get('current_draft_order_id');
        $order = Order::where('company_id', $this->company_id)
            ->where('status', Status::Draft->value)
            ->find($orderId);

        Log::info('Draft order synced', ['session_order_id' => $orderId, 'found' => $order ? $order->id : null]);

        // 3. Status check
        $currentStatus = $order->status instanceof Status ? $order->status->value : (int)$order->status;
        if ($currentStatus !== Status::Draft->value) {
            Cart::destroy();
            Session::forget(['coupon', 'current_draft_order_id']);
            return redirect()->route('order.thankyou', $order->id)->with('success', 'Order already placed.');
        }

        try {
            return DB::transaction(function () use ($request, $orderId, $isCOD) {
                $order = Order::where('company_id', $this->company_id)
                    ->where('id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    throw new \Exception('Order not found.');
                }
                $currentStatus = $order->status instanceof Status ? $order->status->value : (int)$order->status;
                if ($currentStatus !== Status::Draft->value) {
                    return redirect()->route('order.thankyou', $order->id);
                }
                // 4. Address update
                $sourceInfo = [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'fbp' => $_COOKIE['_fbp'] ?? request()->cookie('_fbp') ?? null,
                    'fbc' => $_COOKIE['_fbc'] ?? request()->cookie('_fbc') ?? null,
                    'url' => request()->headers->get('referer') ?? config('app.url'),
                ];

                $order->update([
                    'shipping_address' => [
                        'name'    => $request->name,
                        'phone'   => $request->phone,
                        'email'   => $request->email,
                        'district' => $request->district,
                        'address' => $request->address,
                        'payment_method' => $request->payment_method,
                    ],
                    'pixel_source_info' => $sourceInfo,
                    'status' => Status::Pending->value,
                ]);
                if ($order->customer) {
                    $order->customer->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'district' => $request->district,
                        'address' => $request->address
                    ]);
                }

                // 5. Stock deduct
                $stockResult = $this->finalizeOrderAndDeductStock($order);
                if (!$stockResult['success']) {
                    throw new \Exception($stockResult['error']);
                }
                // 6. Payment create
                $transactionId = $request->transaction_id;
                if ($isCOD) {
                    $transactionId = 'COD-' . ($order->order_no ?? $order->id) . '-' . time();
                }

                $screenshotPaths = [];
                if ($request->hasFile('screenshots')) {
                    foreach ($request->file('screenshots') as $index => $image) {
                        $customFileName = 'order_screenshot_' . ($orderData['order_no'] ?? 'order') . '_' . ($index + 1) . '_' . time();
                        $screenshotPaths[] = FileUploadHelper::uploadImage(
                            $image, 
                            'payments/screenshots',
                            'r2',
                            2048,
                            $customFileName
                        );
                    }
                }

                $senderInfo = array_filter([
                    'sender_number' => $request->sender_number,
                    'screenshot'    => $screenshotPaths,
                    'bank_name'     => $request->bank_name,
                    'branch_name'   => $request->branch_name,
                    'card_type'     => $request->card_type,
                ]);

                if (!$isCOD) {
                    OrderPayment::create([
                        'order_id'       => $order->id,
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $transactionId,
                        'reference_no'   => $request->reference_no,
                        'sender_number'   => $request->sender_number,
                        'amount'         => $request->amount ?? $order->grand_total ?? 0,
                        'change_amount'  => 0,
                        'sender_info'    => $senderInfo,
                        'note'           => $request->note,
                    ]);
                }

                if (Session::has('coupon')) {
                    $couponData = Session::get('coupon');
                    $couponId = $couponData['coupon_id'] ?? null;
                    $couponDiscount = $couponData['discount_amount'] ?? 0;

                    if ($couponId) {
                        $order->update([
                            'coupon_id' => $couponId,
                            'coupon_discount' => $couponDiscount,
                            'grand_total' => max(0, $order->grand_total - $couponDiscount),
                        ]);

                        app(\App\Services\CouponService::class)->applyCoupon(
                            $couponId,
                            $order->id,
                            $order->subtotal ?? $order->grand_total,
                            $couponDiscount,
                            $order->customer_id
                        );
                    }
                }

                $order->update(['payment_status' => $isCOD ? Order::PAYMENT_UNPAID : Order::PAYMENT_PENDING]);

                // 7. Cart tracking
                foreach (Cart::content() as $item) {
                    $cleanProductId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
                    CartTrack::where('session_id', session()->getId())
                        ->where('product_id', $cleanProductId)
                        ->where('variation_id', $item->options->variation_id ?? null)
                        ->update(['status' => CartTrack::PURCHASED]);
                }

                DB::commit();

                Cart::destroy();
                Session::forget(['coupon', 'current_draft_order_id']);

                Log::info('Order confirmed', ['order_id' => $order->id, 'status' => $order->status]);




                return redirect()->route('order.thankyou', $order->id)->with('success', 'Your order has been successfully placed.');
            });
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Final Confirmation Error: ' . $e->getMessage());
            return back()->with('error', 'Order Error: ' . $e->getMessage());
        }
    }

    private function finalizeOrderAndDeductStock($order)
    {
        $statusVal = $order->status instanceof Status ? $order->status->value : (int)$order->status;

        if ($statusVal !== Status::Draft->value) {
            return ['success' => true, 'message' => 'Stock already deducted.'];
        }
        $warehouseData = $order->warehouse_info;

        if (empty($warehouseData)) {
            return ['success' => false, 'error' => 'Warehouse info not found in order.'];
        }

        // try {
        foreach ($warehouseData as $info) {
            $productId   = $info['product_id'];
            $variationId = $info['variation_id'] ?? null;
            $warehouseId = isset($info['warehouse_id']) ? (int) $info['warehouse_id'] : 0;
            $binId       = $info['bin_id'] ?? null;
            $qty         = (int) ($info['quantity'] ?? 0);

            if ($qty <= 0) continue;

            // এখন এই চেকটি আর এরর দিবে না
            if (!$warehouseId || $warehouseId === 0) {
                $product = Product::find($productId);
                if ($product) {
                    $product->decrement('stock_quantity', $qty);
                    $product->decrement('available_stock', $qty);
                }
                continue;
            }

            if ($variationId) {
                // --- VARIATION PRODUCT STOCK DEDUCTION ---
                $variation = ProductVariation::with('product')->find($variationId);
                if (!$variation) continue;

                $product = $variation->product;
                $varStock = ProductVariationStock::where('product_variation_id', $variationId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('bin_id', $binId)
                    ->first();

                if ($varStock) {
                    $qtyBefore = $varStock->quantity;
                    $varStock->decrement('quantity', $qty);
                    $qtyAfter = $varStock->quantity;

                    ProductVariationStockLedger::create([
                        'product_id'       => $product->id,
                        'variation_id'     => $variationId,
                        'warehouse_id'     => $warehouseId,
                        'bin_id'           => $binId,
                        'transaction_type' => 'Sale',
                        'reference_type'   => 'Order',
                        'reference_id'     => $order->id,
                        'quantity_before'  => $qtyBefore,
                        'quantity_change'  => -$qty,
                        'quantity_after'   => $qtyAfter,
                        'notes'            => 'Stock deducted for Order #' . $order->id,
                    ]);
                }
                $product->decrement('stock_quantity', $qty);
                $product->decrement('available_stock', $qty);
            } else {
                // --- SINGLE PRODUCT STOCK DEDUCTION ---
                $product = Product::find($productId);
                if (!$product) continue;

                $qtyBeforeGlobal = $product->stock_quantity;
                $productWarehouseInfo = $product->warehouse_info ?? [];
                $updatedProductInfo = [];

                foreach ($productWarehouseInfo as $pWInfo) {
                    $currentBin = $pWInfo['bin_id'] ?? null;
                    if ($pWInfo['warehouse_id'] == $warehouseId && $currentBin == $binId) {
                        $pWInfo['quantity'] = (int)$pWInfo['quantity'] - $qty;
                    }
                    $updatedProductInfo[] = $pWInfo;
                }

                $product->update([
                    'stock_quantity'  => $product->stock_quantity - $qty,
                    'available_stock' => $product->available_stock - $qty,
                    'warehouse_info'  => $updatedProductInfo
                ]);

                ProductStockLedger::create([
                    'product_id'       => $product->id,
                    'warehouse_id'     => $warehouseId,
                    'bin_id'           => $binId,
                    'transaction_type' => 'Sale',
                    'reference_type'   => 'Order',
                    'reference_id'     => $order->id,
                    'quantity_before'  => $qtyBeforeGlobal,
                    'quantity_change'  => -$qty,
                    'quantity_after'   => $product->stock_quantity,
                    'notes'            => 'Single product stock deducted for Order #' . $order->id,
                ]);
            }
        }
        $order->update(['status' => Status::Pending->value]);
        return ['success' => true];
        // } catch (\Exception $e) {
        //     Log::error('Stock Deduction Failed: ' . $e->getMessage());
        //     return ['success' => false, 'error' => $e->getMessage()];
        // }
    }
    public function orderDetails($id)
    {
        $companyId = $this->company_id;
        $order = Order::where('company_id', $companyId)
            ->with([
                'customer',
                'orderDetails.product' => function ($query) {
                    $query->withTrashed()->select(['id', 'title', 'slug', 'thumbnail']); // This loads deleted products
                },
                'orderDetails.variation',
                'orderDetails.variation.attributes.attributeValue:id,name',
                'orderDetails.variation.attributes.attributeGroup:id,name'
            ])
            ->find($id);

        if (!$order) {
            abort(404);
        }

        // Authorization check
        if (auth('customer')->check() && $order->customer_id !== auth('customer')->id()) {
            abort(403, 'Unauthorized access to this order details.');
        }

        return $this->view('frontend.orderDetails', compact('order'));
    }


    public function invoice($id)
    {
        $companyId = $this->company_id;

        $order =  Order::where('company_id', $companyId)
            ->with([
                'customer:id,name,phone,email,address',
                'orderPayments:id,order_id,amount,payment_method,transaction_id,created_at',
                'orderDetails.product' => function ($q) {
                    $q->withTrashed();
                }, // Add this
                'company'
            ])
            ->findOrFail($id);

        return $this->view('frontend.invoice', compact('order'));
    }

    public function thankyou($id)
    {
        $companyId = $this->company_id;
        $order = Order::where('company_id', $companyId)
            ->with(['orderDetails.product'])
            ->findOrFail($id);

        $productIds = $order->orderDetails->pluck('product_id')->toArray();
        $orderProducts = \App\Models\Product::whereIn('id', $productIds)->get();
        $megaCategoryIds = [];
        foreach ($orderProducts as $p) {
            if ($p->mega_category_ids && is_array($p->mega_category_ids)) {
                $megaCategoryIds = array_merge($megaCategoryIds, $p->mega_category_ids);
            }
        }
        $megaCategoryIds = array_unique($megaCategoryIds);

        $relatedQuery = \App\Models\Product::whereNotIn('id', $productIds)
            ->where('status', \App\Enums\Status::Active->value);

        if (!empty($megaCategoryIds)) {
            $relatedQuery->where(function ($q) use ($megaCategoryIds) {
                foreach ($megaCategoryIds as $catId) {
                    $q->orWhereJsonContains('mega_category_ids', $catId);
                }
            });
        }

        $relatedProducts = $relatedQuery->inRandomOrder()->limit(4)->get();

        $sessionKey = 'pixel_purchase_fired_' . $order->id;
        $pixelPurchaseFired = session()->has($sessionKey);
        if (!$pixelPurchaseFired) {
            session()->put($sessionKey, true);
        }

        return $this->view('frontend.thankyou', compact('order', 'relatedProducts', 'pixelPurchaseFired'));
    }
    public function trackOrder(Request $request)
    {
        $order = null;

        if ($request->filled('order_no')) {
            $order = Order::where('order_no', $request->order_no)
                ->first();

            if (!$order) {
                return back()->with('error', 'Order not found with the provided order number.');
            }
        }

        return $this->view('frontend.product-track', compact('order'));
    }
    public function storeReview(Request $request)
    {
        // 1. Added variation_id to validation
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'variation_id' => 'nullable',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'images'       => 'nullable|array|max:5',
            'images.*'     => 'nullable|image|max:2048'
        ], [
            // Custom error message
            'images.max'   => 'You can only upload a maximum of 5 images.',
        ]);

        if (!auth('customer')->check()) {
            return back()->with('error', 'Please login to review.');
        }

        try {
            $imagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $customFileName = 'order_review_' . ($productId ?? 'product') . '_' . ($index + 1) . '_' . time();
                    $path = FileUploadHelper::uploadImage(
                        $image,
                        'reviews',
                        'r2',
                        2048,
                        $customFileName
                    );
                    $imagePaths[] = $path;
                }
            }

            ProductReview::create([
                'company_id'   => getCurrentCompany()->company_id,
                'product_id'   => $request->product_id,
                'variation_id' => $request->filled('variation_id') ? (int)$request->variation_id : null,
                'customer_id'  => auth('customer')->id(),
                'rating'       => $request->rating,
                'comment'      => $request->comment,
                // FIXED: Removed json_encode because of the 'array' cast in the model
                'images'       => !empty($imagePaths) ? $imagePaths : null,
                'status'       => Status::Pending->value,
            ]);

            return back()->with('success', 'Review submitted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    //return order
    public function requestReturn(Request $request, $id)
    {
        $request->validate([
            'order_detail_ids'   => 'required|array|min:1',
            'order_detail_ids.*' => 'integer',
            'reason'             => 'required|string|max:1000',
            'images.*'           => 'nullable|image|max:2048'
        ]);

        $order = Order::with('orderDetails')->findOrFail($id);

        // Duplicate request আটকানো (order-লেভেলে already ReturnRequest থাকলে)
        if ($order->status == Status::ReturnRequest->value) {
            return back()->with('error', 'Return request already submitted for this order.');
        }

        // Selected order_detail_ids এই order-এরই কিনা যাচাই (security)
        $validItemIds = $order->orderDetails->pluck('id')->toArray();
        $selectedIds = array_intersect($request->order_detail_ids, $validItemIds);

        if (empty($selectedIds)) {
            return back()->with('error', 'Invalid items selected for return.');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $customFileName = 'order_return_' . ($order->order_no ?? 'order') . '_' . ($index + 1) . '_' . time();
                $imagePaths[] = FileUploadHelper::uploadImage(
                    $image, 
                    'returns',
                    'r2',
                    2048,
                    $customFileName
                );
            }
        }

        $returnedItems = $order->orderDetails
            ->whereIn('id', $selectedIds)
            ->map(function ($item) {
                return [
                    'order_detail_id' => $item->id,
                    'product_id'      => $item->product_id,
                    'product_title'   => $item->product->title ?? 'N/A',
                    'quantity'        => $item->quantity,
                ];
            })->values()->toArray();

        $order->update([
            'status' => Status::ReturnRequest->value,
            'return_info' => [
                'items'      => $returnedItems,
                'reason'     => $request->reason,
                'images'     => $imagePaths,
                'requested_at' => now()->toDateTimeString(),
            ]
        ]);

        return back()->with('success', 'Your return request has been submitted.');
    }

    public function submitPayment(Request $request)
    {
        $pm = strtolower($request->payment_method);
        $isCOD = (str_contains($pm, 'cash') || str_contains($pm, 'delivery') || $pm == 'cod');
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'transaction_id' => $isCOD ? 'nullable' : 'required|string|unique:order_payments,transaction_id',
            'amount'         => $isCOD ? 'nullable' : 'required|numeric|min:1',
            'reference_no'   => 'nullable|string|max:100',
            'sender_number'  => 'nullable|string|max:20',
            'note'           => 'nullable|string|max:500',
            'bank_name'      => 'nullable|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'card_type'      => 'nullable|string|max:50',
            'screenshots' => 'nullable|array|max:3',
            'screenshots.*'  => 'image|mimes:jpeg,png,jpg,gif,,webp|max:2048',
        ], [
            'transaction_id.unique' => 'This transaction ID has already been used. Please check and try again.',
            'transaction_id.required' => 'Transaction ID is required.',
        ]);

        try {
            $draftIdFromSession = Session::get('current_draft_order_id');
            $order = Order::where('id', $request->order_id);


            if (auth('customer')->check()) {
                $order->where('customer_id', auth('customer')->id());
            } else {
                $order->where('id', $draftIdFromSession);
            }
            $order = $order->firstOrFail();

            $pm = strtolower($request->payment_method);
            $isCOD = (str_contains($pm, 'cash') || str_contains($pm, 'delivery') || $pm == 'cod');

            $transactionId = $request->transaction_id;
            if ($isCOD) {
                $transactionId = 'COD-' . $order->order_no . '-' . time();
            }
            $screenshotPaths = [];
            if ($request->hasFile('screenshots')) {
                foreach ($request->file('screenshots') as $index => $image) {
                    $customFileName = 'order_payment_screenshot_' . ($order->order_no ?? 'order') . '_' . ($index + 1) . '_' . time();
                    $path = FileUploadHelper::uploadImage(
                        $image, 
                        'payments/screenshots',
                        'r2',
                        2048,
                        $customFileName
                    );
                    $screenshotPaths[] = $path;
                }
            }

            // All extra sender/payment info stored as JSON
            $senderInfo = array_filter([
                'sender_number' => $request->sender_number,
                'screenshot'    => $screenshotPaths,
                'bank_name'     => $request->bank_name,
                'branch_name'   => $request->branch_name,
                'card_type'     => $request->card_type,
            ]);
            Log::info("Submitting payment for Order ID: {$order->id}, Payment Method: {$request->payment_method}, Transaction ID: {$transactionId}", $senderInfo);

            OrderPayment::create([
                'order_id'       => $order->id,
                'payment_method' => $request->payment_method,
                'transaction_id' => $transactionId,
                'reference_no'   => $request->reference_no,
                'amount'         => $isCOD ? $order->grand_total : $request->amount,
                'change_amount'  => 0,
                'sender_info'    => $senderInfo,
                'note'           => $request->note,
            ]);

            $order->update(['payment_status' => Order::PAYMENT_PENDING]);

            if ($order->status == Status::Draft->value) {
                $order->update(['status' => Status::Pending->value]);

                $this->finalizeOrderAndDeductStock($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment submitted! Our team will verify it soon.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

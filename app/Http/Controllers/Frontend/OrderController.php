<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\{Cart as CartTrack, CustomerPaymentMethod, Order, OrderPayment, Party, Product, ProductReview, ProductStockLedger, ProductVariation, ProductVariationStock, ProductVariationStockLedger, SiteSetting, Warehouse};
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
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
            ->select(['id', 'company_id', 'name', 'account_number', 'phone', 'method_details', 'status'])->get();

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
                'address' => $data['address'] ?? $customer->address ?? 'N/A',
            ];

            $oldId    = Session::get('current_draft_order_id');
            $oldOrder = $oldId
                ? Order::where('id', $oldId)->where('status', Status::Draft->value)->first()
                : null;

            if ($oldOrder) {
                $oldOrder->update([
                    'customer_id'      => $customer->id,
                    'shipping_address' => $shippingAddress,
                    'other_charges'    => session()->get('shipping_cost', 60),
                    'warehouse_info'   => $warehouseInfo,
                    'items'            => $items,
                ]);
                DB::commit();
                return ['success' => true, 'order_id' => $oldOrder->id, 'logged_in' => true];
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
            'address'        => 'required',
            'payment_method' => 'required|string',
            'transaction_id' => $isCOD ? 'nullable' : 'required|string|unique:order_payments,transaction_id',
            'amount'         => $isCOD ? 'nullable' : 'required|numeric',
            'screenshots'    => 'nullable|array|max:3',
            'screenshots.*'  => 'image|max:2048',
        ]);

        // 1. Draft order find
        $orderId = Session::get('current_draft_order_id');
        $order = Order::where('company_id', $this->company_id)
            ->where('status', Status::Draft->value)
            ->find($orderId);

        Log::info('Draft order search', ['session_order_id' => $orderId, 'found' => $order ? $order->id : null]);

        // 2. Fallback — draft না থাকলে create করো
        if (!$order) {
            $draftResult = $this->createDraftOrder($request->all());
            if ($draftResult['success']) {
                $order = Order::find(Session::get('current_draft_order_id'));
            } else {
                return back()->with('error', 'Order failed: ' . $draftResult['error']);
            }
        }

        // 3. Status check
        $currentStatus = $order->status instanceof Status ? $order->status->value : (int)$order->status;
        if ($currentStatus !== Status::Draft->value) {
            Cart::destroy();
            Session::forget(['coupon', 'current_draft_order_id']);
            return redirect()->route('order.invoice', $order->id)->with('success', 'Order already placed.');
        }

        try {
            DB::beginTransaction();

            // 4. Address update
            $order->update([
                'shipping_address' => [
                    'name'    => $request->name,
                    'phone'   => $request->phone,
                    'address' => $request->address,
                    'payment_method' => $request->payment_method,
                ],
                'status' => Status::Pending->value,
            ]);

            if ($order->customer) {
                $order->customer->update(['name' => $request->name, 'address' => $request->address]);
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
                foreach ($request->file('screenshots') as $image) {
                    $screenshotPaths[] = FileUploadHelper::uploadImage($image, 'payments/screenshots');
                }
            }

            $senderInfo = array_filter([
                'sender_number' => $request->sender_number,
                'screenshot'    => $screenshotPaths,
                'bank_name'     => $request->bank_name,
                'branch_name'   => $request->branch_name,
                'card_type'     => $request->card_type,
            ]);

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

            return redirect()->route('order.invoice', $order->id)->with('success', 'Your order has been successfully placed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Final Confirmation Error: ' . $e->getMessage());
            return back()->with('error', 'Order Error: ' . $e->getMessage());
        }
    }

    private function finalizeOrderAndDeductStock($order)
    {
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
        $ttl = now()->addHours(6);

        $order = Cache::remember("order_details_view_{$id}", $ttl, function () use ($id, $companyId) {
            return Order::where('company_id', $companyId)
                ->with([
                    'customer',
                    'orderDetails.product' => function ($query) {
                        $query->withTrashed()->select(['id', 'title', 'slug', 'thumbnail']); // This loads deleted products
                    },
                    'orderDetails.variation:id,product_id,display_name,regular_price,sale_price',
                    'orderDetails.variation.attributes.attributeValue:id,name',
                    'orderDetails.variation.attributes.attributeGroup:id,name'
                ])
                ->find($id);
        });
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
        $ttl = now()->addHours(6);
        $order = Cache::remember("order_invoice_view_{$id}", $ttl, function () use ($id, $companyId) {
        return Order::where('company_id', $companyId)
            ->with([
            'customer:id,name,phone,email,address',
            'orderPayments:id,order_id,amount,payment_method,transaction_id,created_at',
            'orderDetails.product' => function ($q) {
                $q->withTrashed();
            }, // Add this
            'company'
        ])
            ->findOrFail($id);
        });
        return $this->view('frontend.invoice', compact('order'));
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
                foreach ($request->file('images') as $image) {
                    $path = FileUploadHelper::uploadImage(
                        $image,
                        'reviews',

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
            'reason'   => 'required|string|max:1000',
            'images.*' => 'nullable|image|max:2048'
        ]);

        $order = Order::findOrFail($id);

        // Prevent duplicate requests
        if ($order->status == Status::ReturnRequest->value) {
            return back()->with('error', 'Return request already submitted.');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = FileUploadHelper::uploadImage($image, 'returns',);
            }
        }

        $order->update([
            'status' => Status::ReturnRequest->value,
            'return_info' => [
                'reason'     => $request->reason,
                'images'     => $imagePaths,
                'request_at' => now()->toDateTimeString(),
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
                foreach ($request->file('screenshots') as $image) {
                    $path = FileUploadHelper::uploadImage($image, 'payments/screenshots');
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

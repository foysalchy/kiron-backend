<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Helpers\NetworkHelper;
use App\Http\Controllers\Controller;
use App\Models\{Cart as CartTrack, ContentSetting, CustomerPaymentMethod, IpDirectory, IpSetting, Order, OrderNote, OrderPayment, Party, Product, ProductReview, ProductStockLedger, ProductVariation, ProductVariationStock, ProductVariationStockLedger, SiteSetting, Warehouse};
use App\Services\FraudProtectionService;
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Cache, DB, Hash, Log, Session, View};

class OrderController extends FrontendController
{
    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }

    public function index()
    {
        // 0. Fraud Protection: Blocked IP Check (Checkout page blocked, browsing allowed)
        $clientIp = NetworkHelper::getClientIp();
        $isBlocked = IpDirectory::withoutGlobalScopes()
            ->where('company_id', $this->company_id)
            ->where('ip_address', $clientIp)
            ->where('status', 0) // 0 = Blocked
            ->exists();

        if ($isBlocked) {
            $msg = 'দুঃখিত, আপনার নেটওয়ার্ক থেকে চেকআউট সাময়িকভাবে স্থগিত রয়েছে। অনুগ্রহ করে আমাদের হেল্পলাইনে যোগাযোগ করুন।';
            return redirect()->route('cart.index')->with('error', $msg);
        }

        $setup = SiteSetting::where('company_id', $this->company_id)->first();
        if (($setup->login_required_for_checkout ?? 0) == 1 && !auth('customer')->check()) {
                    $previewTheme = request('preview_theme');
            $intendedUrl = $previewTheme
                ? route('checkout.index', ['preview_theme' => $previewTheme])
                : route('checkout.index');
            session()->put('url.intended', $intendedUrl);

            $msg = (isset($setup->lang) && $setup->lang == 'bn')
                ? 'চেকআউট সম্পন্ন করতে অনুগ্রহ করে প্রথমে লগইন করুন।'
                : 'Please login to proceed with checkout.';

            $redirectParams = $previewTheme ? ['preview_theme' => $previewTheme] : [];
            return redirect()->route('user.login', $redirectParams)->with('warning', $msg);
        }

        if (Cart::count() == 0) {
                    $previewTheme = request('preview_theme');
            $params = $previewTheme ? ['preview_theme' => $previewTheme] : [];
            return redirect()->route('cart.index', $params)->with('error', 'Your cart is empty!');
        }
        $paymentMethods = \Illuminate\Support\Facades\Cache::remember("payment_methods_{$this->company_id}", 86400, function () {
            return CustomerPaymentMethod::where('company_id', $this->company_id)
                ->where('status', Status::Active->value)
                ->select(['id', 'company_id', 'name', 'icon', 'account_number', 'method_details', 'status'])
                ->get();
        });

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

        $settings = \Illuminate\Support\Facades\Cache::remember("site_settings_{$this->company_id}", 86400, function () {
            return SiteSetting::where('company_id', $this->company_id)
                ->select(['id', 'company_id', 'inside_charge', 'outside_charge', 'free_shipping_rules', 'free_shipping_text'])
                ->first();
        });

        $defaultInside = $settings->inside_charge ?? 60;
        $cartContent = Cart::content();


        $subtotal = (float) str_replace(',', '', Cart::subtotal());
        $shipping = session()->get('shipping_cost', $defaultInside);
        $shipping_area = session()->get('shipping_area', 'inside');

        $is_product_free_delivery = $cartContent->isNotEmpty();
        foreach ($cartContent as $item) {
            $pId = $item->id;
            if (str_starts_with($pId, 'var_')) {
                $varId = str_replace('var_', '', $pId);
                $variation = ProductVariation::find($varId);
                $pId = $variation ? $variation->product_id : null;
            }
            if ($pId) {
                $product = Product::find($pId);
                if ($product && !$product->is_free_delivery) {
                    $is_product_free_delivery = false;
                    break;
                }
            }
        }

        $is_rule_free_shipping = false;
        if ($is_product_free_delivery) {
            $shipping = 0;
            $is_free_delivery = true;
        } elseif ($settings && method_exists($settings, 'isEligibleForFreeShipping')) {
            $totalQty = (int) Cart::count();
            if ($settings->isEligibleForFreeShipping($totalQty, $subtotal, false)) {
                $is_rule_free_shipping = true;
                $is_free_delivery = true;
                $shipping = 0;
            } else {
                $is_free_delivery = false;
            }
        } else {
            $is_free_delivery = false;
        }

        $discount = session()->has('coupon') ? session('coupon')['discount_amount'] : 0;
        $total = ($subtotal - $discount) + $shipping;

        $draftOrderId = Session::get('current_draft_order_id');

        // dd($cartContent, $subtotal, $discount, $shipping, $total, $shipping_area, $paymentMethods, $draftOrderId);
        return $this->view('frontend.checkout', compact(
            'setup',
            'cartContent',
            'subtotal',
            'discount',
            'shipping',
            'total',
            'shipping_area',
            'paymentMethods',
            'draftOrderId',
            'is_free_delivery',
            'is_product_free_delivery',
            'is_rule_free_shipping'
        ));
    }

    public function partialSave(Request $request)
    {
        $clientIp = NetworkHelper::getClientIp();
        $isBlocked = IpDirectory::withoutGlobalScopes()
            ->where('company_id', $this->company_id)
            ->where('ip_address', $clientIp)
            ->where('status', 0)
            ->exists();
        if ($isBlocked) {
            return response()->json(['success' => false, 'message' => 'Blocked IP']);
        }

        $rawPhone = preg_replace('/[^0-9]/', '', (string)$request->phone);
        if (str_starts_with($rawPhone, '8801')) {
            $rawPhone = substr($rawPhone, 2);
        }
        if (!preg_match('/^01[3-9][0-9]{8}$/', $rawPhone)) {
            return response()->json(['success' => false, 'message' => 'Invalid Bangladeshi phone number']);
        }
        $request->merge(['phone' => $rawPhone]);

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
                $oldOrder->orderDetails()->forceDelete();
                $oldOrder->forceDelete();
            }

            $shipping_cost = session()->get('shipping_cost', 60);
            $is_free_delivery = $cartContent->isNotEmpty();
            foreach ($cartContent as $item) {
                $pId = $item->id;
                if (str_starts_with($pId, 'var_')) {
                    $varId = str_replace('var_', '', $pId);
                    $variation = ProductVariation::find($varId);
                    $pId = $variation ? $variation->product_id : null;
                }
                if ($pId) {
                    $product = Product::find($pId);
                    if ($product && !$product->is_free_delivery) {
                        $is_free_delivery = false;
                        break;
                    }
                }
            }

            if ($is_free_delivery) {
                $shipping_cost = 0;
            } else {
                $settings = \App\Models\SiteSetting::where('company_id', $this->company_id)->first();
                if ($settings && method_exists($settings, 'isEligibleForFreeShipping')) {
                    $totalQty = (int) Cart::count();
                    $subtotal = (float) str_replace(',', '', Cart::subtotal());
                    if ($settings->isEligibleForFreeShipping($totalQty, $subtotal, false)) {
                        $is_free_delivery = true;
                        $shipping_cost = 0;
                    }
                }
            }

            $orderData = [

                'warehouse_info'   => $warehouseInfo, // JSON Column Store
                'customer_id'      => $customer->id,
                'company_id'       => $this->company_id,
                'items'            => $items,
                'status'           => Status::Draft->value,
                'order_date'       => now(),
                'other_charges'    => $shipping_cost,
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

        $setup = SiteSetting::where('company_id', $this->company_id)->first();
        if (($setup->login_required_for_checkout ?? 0) == 1 && !auth('customer')->check()) {
                    $previewTheme = request('preview_theme');
            $intendedUrl = $previewTheme
                ? route('checkout.index', ['preview_theme' => $previewTheme])
                : route('checkout.index');
            session()->put('url.intended', $intendedUrl);

            $msg = (isset($setup->lang) && $setup->lang == 'bn')
                ? 'চেকআউট সম্পন্ন করতে অনুগ্রহ করে প্রথমে লগইন করুন।'
                : 'Please login to proceed with checkout.';

            $redirectParams = $previewTheme ? ['preview_theme' => $previewTheme] : [];
            return redirect()->route('user.login', $redirectParams)->with('warning', $msg);
        }

        $pm = strtolower($request->payment_method ?? '');
        $isCOD = (str_contains($pm, 'cash') || str_contains($pm, 'delivery') || $pm == 'cod');

        // Fraud, Honeypot & Rate Limit Protection
        $fraudService = app(FraudProtectionService::class);
        $fraudCheck = $fraudService->validateOrderSubmission($request, $this->company_id);
        if (!$fraudCheck['allowed']) {
            if (!empty($fraudCheck['already_placed'])) {
                $recentId = $fraudCheck['recent_order_id'] ?? null;
                $previewTheme = request('preview_theme');
                $noticeParams = array_filter(['id' => $recentId, 'preview_theme' => $previewTheme]);
                $noticeUrl = route('order.already_placed', $noticeParams);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'        => true,
                        'already_placed' => true,
                        'order_id'       => $recentId,
                        'redirect_url'   => $noticeUrl,
                        'message'        => 'আপনি ইতিমধ্যে সম্প্রতি একটি অর্ডার সম্পন্ন করেছেন।',
                    ]);
                }
                return redirect()->to($noticeUrl);
            }

            $msg = $fraudCheck['message'] ?? 'Order cannot be placed at this time.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }
        if (!empty($fraudCheck['clean_phone'])) {
            $request->merge(['phone' => $fraudCheck['clean_phone']]);
        }

        $request->validate([
            'name'           => 'required',
            'phone'          => 'required',
            'email'          => 'nullable|email',
            'district'       => ($setup->checkout_show_city ?? true) ? 'required|string' : 'nullable|string',
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

        $otpService = app(\App\Services\CheckoutOtpService::class);
        $isOtpEnabled = $otpService->isOtpEnabled($this->company_id);
        $hasSmsBalance = $isOtpEnabled ? $otpService->hasSufficientBalance($this->company_id) : false;

        $isOtpFallbackNoBalance = false;
        if ($isOtpEnabled && !$hasSmsBalance) {
            Log::info("Order #{$orderId}: SMS wallet balance is 0 for company {$this->company_id}. Bypassing OTP checkout, proceeding directly with 'Not Verified' note.");
            $isOtpEnabled = false;
            $isOtpFallbackNoBalance = true;
        }

        if ($isOtpEnabled) {
            try {
                return DB::transaction(function () use ($request, $orderId, $isCOD, $otpService, $fraudService) {
                    $order = Order::where('company_id', $this->company_id)
                        ->where('id', $orderId)
                        ->lockForUpdate()
                        ->first();

                    if (!$order) {
                        throw new \Exception('Order not found.');
                    }

                    $marketingSource = session('marketing_source') ?? (session('fbclid') ? 'Facebook Ads' : null);
                    $finalSource = $marketingSource ? (str_contains(strtolower($marketingSource), 'facebook') ? 'facebook' : strtolower($marketingSource)) : 'website';

                    $ua = request()->userAgent();
                    $ip = \App\Helpers\NetworkHelper::getClientIp();
                    $deviceInfo = \App\Helpers\DeviceHelper::detect($ua);
                    $networkInfo = \App\Helpers\NetworkHelper::detect($ip);

                    $sourceInfo = [
                        'ip'         => $ip,
                        'device'     => $deviceInfo['device'],
                        'platform'   => $deviceInfo['platform'],
                        'browser'    => $deviceInfo['browser'],
                        'network'    => $networkInfo['network'],
                        'isp'        => $networkInfo['isp'],
                        'user_agent' => $ua,
                        'fbp'        => $_COOKIE['_fbp'] ?? request()->cookie('_fbp') ?? null,
                        'fbc'        => $_COOKIE['_fbc'] ?? request()->cookie('_fbc') ?? session('_fbc'),
                        'url'        => session('marketing_landing_url') ?? request()->headers->get('referer') ?? config('app.url'),
                        'source'     => $marketingSource ?? 'Direct/Organic',
                        'utm_source' => session('utm_source'),
                        'utm_medium' => session('utm_medium'),
                        'utm_campaign' => session('utm_campaign'),
                        'utm_content' => session('utm_content'),
                    ];

                    $order->update([
                        'shipping_address' => [
                            'name'           => $request->name,
                            'phone'          => $request->phone,
                            'email'          => $request->email,
                            'district'       => $request->district,
                            'address'        => $request->address,
                            'payment_method' => $request->payment_method,
                        ],
                        'source'            => $order->source ?: $finalSource,
                        'source_info'       => $sourceInfo,
                        'pixel_source_info' => $sourceInfo,
                        'status'            => Status::Draft->value,
                        'is_otp_verified'   => false,
                    ]);

                    if ($order->customer) {
                        $order->customer->update([
                            'name'     => $request->name,
                            'email'    => $request->email,
                            'district' => $request->district,
                            'address'  => $request->address
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
                        }
                    }

                    $order->update(['payment_status' => $isCOD ? Order::PAYMENT_UNPAID : Order::PAYMENT_PENDING]);

                    $otpResult = $otpService->sendOtp($order, $request->phone);

                    if (!$otpResult['success']) {
                        if ($otpResult['error'] === 'sms_limit_exceeded' || $otpResult['error'] === 'ip_blocked' || $otpResult['error'] === 'ip_sms_limit_exceeded') {
                            throw new \Exception($otpResult['message'] ?? 'Failed to send OTP.');
                        }

                        // Gateway error or insufficient gateway balance -> Fallback to direct order!
                        Log::warning("Order #{$order->id}: OTP SMS failed ({$otpResult['error']}). Falling back to direct order with 'Not Verified' note.");

                        $order->update([
                            'status'          => Status::Pending->value,
                            'is_otp_verified' => false,
                            'note'            => 'Not Verified',
                        ]);

                        OrderNote::create([
                            'company_id' => $this->company_id,
                            'order_id'   => $order->id,
                            'note'       => 'Not Verified',
                            'type'       => 'system',
                            'status'     => 1,
                        ]);

                        $stockResult = $this->finalizeOrderAndDeductStock($order);
                        if (!$stockResult['success']) {
                            Log::warning('Stock deduction warning on fallback direct order: ' . ($stockResult['error'] ?? ''));
                        }

                        $transactionId = $request->transaction_id;
                        if ($isCOD) {
                            $transactionId = 'COD-' . ($order->order_no ?? $order->id) . '-' . time();
                        }

                        $screenshotPaths = [];
                        if ($request->hasFile('screenshots')) {
                            foreach ($request->file('screenshots') as $index => $image) {
                                $customFileName = 'order-screenshot-' . ($order->order_no ?? 'order') . '-' . ($index + 1) . '-' . time();
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
                                'sender_number'  => $request->sender_number,
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
                                app(\App\Services\CouponService::class)->applyCoupon(
                                    $couponId,
                                    $order->id,
                                    $order->subtotal ?? $order->grand_total,
                                    $couponDiscount,
                                    $order->customer_id
                                );
                            }
                        }

                        foreach (Cart::content() as $item) {
                            $cleanProductId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
                            CartTrack::where('session_id', session()->getId())
                                ->where('product_id', $cleanProductId)
                                ->where('variation_id', $item->options->variation_id ?? null)
                                ->update(['status' => CartTrack::PURCHASED]);
                        }

                        Cart::destroy();
                        Session::forget([
                            'coupon',
                            'current_draft_order_id',
                            'marketing_source',
                            'utm_source',
                            'utm_medium',
                            'utm_campaign',
                            'utm_content',
                            'fbclid',
                            '_fbc',
                            '_ttp',
                            'marketing_landing_url'
                        ]);

                        app(FraudProtectionService::class)->recordOrderPlaced($this->company_id, $request->phone, null, $order->id);

                        $previewTheme = request('preview_theme');
                        $thankYouParams = array_filter(['id' => $order->id, 'preview_theme' => $previewTheme]);
                        $thankYouUrl = route('order.thankyou', $thankYouParams);

                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success'          => true,
                                'requires_otp'     => false,
                                'redirect_url'     => $thankYouUrl,
                                'thankyou_url'     => $thankYouUrl,
                                'invoice_url'      => route('order.invoice', $order->id),
                                'order_id'         => $order->id,
                                'order_code'       => $order->order_no ?? ('#' . $order->id),
                                'message'          => 'Your order has been successfully placed.',
                                'instant_purchase' => true,
                            ]);
                        }

                        return redirect()->to($thankYouUrl)->with('success', 'Your order has been successfully placed.');
                    }

                    app(FraudProtectionService::class)->recordOrderPlaced($this->company_id, $request->phone, null, $order->id);

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success'          => true,
                            'requires_otp'     => true,
                            'order_id'         => $order->id,
                            'order_code'       => $order->order_no ?? ('#' . $order->id),
                            'phone'            => $otpResult['phone'] ?? $request->phone,
                            'message'          => $otpResult['message'] ?? 'আপনার নাম্বারে একটি ভেরিফিকেশন ওটিপি পাঠানো হয়েছে।',
                            'instant_purchase' => false,
                        ]);
                    }

                    session()->put('pending_otp_order_id', $order->id);
                    return back()->with([
                        'requires_otp' => true,
                        'order_id'     => $order->id,
                        'phone'        => $otpResult['phone'] ?? $request->phone,
                        'info'         => 'আপনার নাম্বারে একটি ভেরিফিকেশন ওটিপি পাঠানো হয়েছে।',
                    ]);
                });
            } catch (\Exception $e) {
                Log::error('OTP Order Store Error: ' . $e->getMessage());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                return back()->with('error', $e->getMessage());
            }
        }

        // 3. Status check
        $currentStatus = $order->status instanceof Status ? $order->status->value : (int)$order->status;
        if ($currentStatus !== Status::Draft->value) {
            Cart::destroy();
            Session::forget(['coupon', 'current_draft_order_id']);
                    $previewTheme = request('preview_theme');
            $p = array_filter(['id' => $order->id, 'preview_theme' => $previewTheme]);
            return redirect()->route('order.thankyou', $p)->with('success', 'Order already placed.');
        }

        try {
            return DB::transaction(function () use ($request, $orderId, $isCOD, $fraudService, $isOtpFallbackNoBalance) {
                $order = Order::where('company_id', $this->company_id)
                    ->where('id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    throw new \Exception('Order not found.');
                }
                $currentStatus = $order->status instanceof Status ? $order->status->value : (int)$order->status;
                if ($currentStatus !== Status::Draft->value) {
                            $previewTheme = request('preview_theme');
                    $p = array_filter(['id' => $order->id, 'preview_theme' => $previewTheme]);
                    return redirect()->route('order.thankyou', $p);
                }
                $marketingSource = session('marketing_source') ?? (session('fbclid') ? 'Facebook Ads' : null);
                $finalSource = $marketingSource ? (str_contains(strtolower($marketingSource), 'facebook') ? 'facebook' : strtolower($marketingSource)) : 'website';

                $ua = request()->userAgent();
                $ip = \App\Helpers\NetworkHelper::getClientIp();
                $deviceInfo = \App\Helpers\DeviceHelper::detect($ua);
                $networkInfo = \App\Helpers\NetworkHelper::detect($ip);

                $sourceInfo = [
                    'ip'         => $ip,
                    'device'     => $deviceInfo['device'],
                    'platform'   => $deviceInfo['platform'],
                    'browser'    => $deviceInfo['browser'],
                    'network'    => $networkInfo['network'],
                    'isp'        => $networkInfo['isp'],
                    'user_agent' => $ua,
                    'fbp'        => $_COOKIE['_fbp'] ?? request()->cookie('_fbp') ?? null,
                    'fbc'        => $_COOKIE['_fbc'] ?? request()->cookie('_fbc') ?? session('_fbc'),
                    'url'        => session('marketing_landing_url') ?? request()->headers->get('referer') ?? config('app.url'),
                    'source'     => $marketingSource ?? 'Direct/Organic',
                    'utm_source' => session('utm_source'),
                    'utm_medium' => session('utm_medium'),
                    'utm_campaign' => session('utm_campaign'),
                    'utm_content' => session('utm_content'),
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
                    'source' => $order->source ?: $finalSource,
                    'source_info' => $sourceInfo,
                    'pixel_source_info' => $sourceInfo,
                    'status' => Status::Pending->value,
                    'is_otp_verified' => $isOtpFallbackNoBalance ? false : null,
                    'note' => $isOtpFallbackNoBalance ? 'Not Verified' : ($request->note ?? null),
                ]);

                if ($isOtpFallbackNoBalance) {
                    OrderNote::create([
                        'company_id' => $this->company_id,
                        'order_id'   => $order->id,
                        'note'       => 'Not Verified',
                        'type'       => 'system',
                        'status'     => 1,
                    ]);
                }
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
                        $customFileName = 'order-screenshot-' . ($order->order_no ?? 'order') . '-' . ($index + 1) . '-' . time();
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

                app(FraudProtectionService::class)->recordOrderPlaced($this->company_id, $request->phone, null, $order->id);

                Cart::destroy();
                Session::forget([
                    'coupon',
                    'current_draft_order_id',
                    'marketing_source',
                    'utm_source',
                    'utm_medium',
                    'utm_campaign',
                    'utm_content',
                    'fbclid',
                    '_fbc',
                    '_ttp',
                    'marketing_landing_url'
                ]);

                Log::info('Order confirmed', ['order_id' => $order->id, 'status' => $order->status]);




                        $previewTheme = request('preview_theme');
                $thankYouParams = array_filter(['id' => $order->id, 'preview_theme' => $previewTheme]);
                $thankYouUrl = route('order.thankyou', $thankYouParams);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'redirect_url' => $thankYouUrl,
                        'order_id'     => $order->id,
                        'message'      => 'Your order has been successfully placed.',
                    ]);
                }

                return redirect()->to($thankYouUrl)->with('success', 'Your order has been successfully placed.');
            });
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Final Confirmation Error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Order Error: ' . $e->getMessage()], 422);
            }
            return back()->with('error', 'Order Error: ' . $e->getMessage());
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'otp'      => 'required|string',
        ]);

        $otpService = app(\App\Services\CheckoutOtpService::class);
        $result = $otpService->verifyOtp((int) $request->order_id, $request->otp, $this->company_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        $order = $result['order'];

        if ($order->type === Order::TYPE_LANDING) {
            $productService = app(\App\Services\ProductService::class);
            $warehouseInfoList = $order->warehouse_info ?? [];
            foreach ($warehouseInfoList as $wInfo) {
                $itemProdId = $wInfo['product_id'] ?? null;
                $itemProdObj = \App\Models\Product::find($itemProdId);
                if ($itemProdObj && $itemProdObj->manage_stock) {
                    $stockData = [
                        'warehouse_id'     => $wInfo['warehouse_id'],
                        'bin_id'           => null,
                        'quantity'         => (int) $wInfo['quantity'],
                        'batch_number'     => null,
                        'serial_numbers'   => null,
                        'transaction_type' => 'sale',
                        'reference_type'   => 'LandingOrder',
                        'reference_id'     => $order->id,
                        'notes'            => "Stock deducted for order: {$order->order_no}"
                    ];
                    if (!empty($wInfo['variation_id'])) {
                        $stockData['variation_id'] = $wInfo['variation_id'];
                    }
                    $productService->removeStockFromWarehouse($itemProdId, $stockData);
                }
            }
        } else {
            $stockResult = $this->finalizeOrderAndDeductStock($order);
            if (!$stockResult['success']) {
                Log::warning('Stock deduction warning on verifyOtp: ' . ($stockResult['error'] ?? ''));
            }

            if (Session::has('coupon')) {
                $couponData = Session::get('coupon');
                $couponId = $couponData['coupon_id'] ?? null;
                $couponDiscount = $couponData['discount_amount'] ?? 0;

                if ($couponId) {
                    app(\App\Services\CouponService::class)->applyCoupon(
                        $couponId,
                        $order->id,
                        $order->subtotal ?? $order->grand_total,
                        $couponDiscount,
                        $order->customer_id
                    );
                }
            }

            foreach (\Cart::content() as $item) {
                $cleanProductId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
                CartTrack::where('session_id', session()->getId())
                    ->where('product_id', $cleanProductId)
                    ->where('variation_id', $item->options->variation_id ?? null)
                    ->update(['status' => CartTrack::PURCHASED]);
            }

            \Cart::destroy();
            Session::forget([
                'coupon',
                'current_draft_order_id',
                'marketing_source',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'fbclid',
                '_fbc',
                '_ttp',
                'marketing_landing_url'
            ]);
        }

        app(FraudProtectionService::class)->recordOrderPlaced($order->company_id, $order->customer?->phone ?? ($order->shipping_address['phone'] ?? ''));

        $previewTheme = request('preview_theme');
        $thankYouParams = array_filter(['id' => $order->id, 'preview_theme' => $previewTheme]);
        $thankYouUrl = route('order.thankyou', $thankYouParams);

        return response()->json([
            'success'          => true,
            'message'          => 'আপনার অর্ডারটি সফলভাবে ভেরিফাই ও কনফার্ম হয়েছে!',
            'order_id'         => $order->id,
            'order_code'       => $order->order_no ?? ('#' . $order->id),
            'redirect_url'     => $thankYouUrl,
            'thankyou_url'     => $thankYouUrl,
            'invoice_url'      => route('order.invoice', $order->id),
            'instant_purchase' => true,
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $otpService = app(\App\Services\CheckoutOtpService::class);
        $result = $otpService->resendOtp((int) $request->order_id, $this->company_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'নতুন ওটিপি পাঠানো সম্ভব হয়নি।',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'আপনার নাম্বারে নতুন একটি ওটিপি কোড পাঠানো হয়েছে।',
        ]);
    }

    public function finalizeOrderAndDeductStock($order)
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
                    $query->withTrashed()->select(['id', 'title', 'slug', 'thumbnail', 'thumbnail_310', 'thumbnail_95']); // This loads deleted products
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

        $invoiceFooter = ContentSetting::where('company_id', $companyId)
            ->where('page_type', ContentSetting::INVOICE_FOOTER)
            ->where('status', Status::Active->value)
            ->first();

        return $this->view('frontend.invoice', compact('order', 'invoiceFooter'));
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
        if ($order->is_otp_verified === false) {
            $pixelPurchaseFired = true; // suppress purchase pixel event if unverified
        } elseif (!$pixelPurchaseFired) {
            session()->put($sessionKey, true);
        }

        return $this->view('frontend.thankyou', compact('order', 'relatedProducts', 'pixelPurchaseFired'));
    }

    public function alreadyPlacedNotice(Request $request, $id = null)
    {
        $companyId = $this->company_id;
        $order = null;

        if ($id && is_numeric($id)) {
            $order = Order::where('company_id', $companyId)
                ->with(['customer', 'orderDetails.product'])
                ->find($id);
        }

        if (!$order) {
            $lastOrderId = session('last_placed_order_id');
            if ($lastOrderId) {
                $order = Order::where('company_id', $companyId)
                    ->with(['customer', 'orderDetails.product'])
                    ->find($lastOrderId);
            }
        }

        if (!$order) {
            $clientIp = NetworkHelper::getClientIp();
            $order = Order::where('company_id', $companyId)
                ->with(['customer', 'orderDetails.product'])
                ->where(function ($q) use ($clientIp) {
                    $q->whereJsonContains('source_info->ip', $clientIp)
                      ->orWhereJsonContains('pixel_source_info->ip', $clientIp);
                })
                ->latest('id')
                ->first();
        }

        $relatedProducts = collect();
        if ($order) {
            $productIds = $order->orderDetails->pluck('product_id')->toArray();
            $relatedProducts = \App\Models\Product::where('company_id', $companyId)
                ->whereNotIn('id', $productIds)
                ->where('status', \App\Enums\Status::Active->value)
                ->inRandomOrder()
                ->limit(5)
                ->get();
        } else {
            $relatedProducts = \App\Models\Product::where('company_id', $companyId)
                ->where('status', \App\Enums\Status::Active->value)
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }

        $setup = SiteSetting::where('company_id', $companyId)->first();

        return $this->view('frontend.already-placed', compact('order', 'relatedProducts', 'setup'));
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
                    $customFileName = 'order_review_' . ($productId ?? 'product') . '_' . ($index + 1) . '-' . time();
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
                $customFileName = 'order_return_' . ($order->order_no ?? 'order') . '_' . ($index + 1) . '-' . time();
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
                    $customFileName = 'order_payment_screenshot_' . ($order->order_no ?? 'order') . '_' . ($index + 1) . '-' . time();
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

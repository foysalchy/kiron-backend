<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Illuminate\Support\Facades\Hash;
use App\Services\OrderService;

class LandingController extends FrontendController
{
    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }
    public function index($slug)
    {
        $companyId = $this->company_id;
        $user = auth()->user() ?? auth('sanctum')->user() ?? request()->user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || !empty($user->is_super_admin) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()));
        if (!$companyId && $user && !$isSuperAdmin && $user->company_id) {
            $companyId = $user->company_id;
        }

        $ttl = now()->addHours(6);
        $landing = Cache::remember("landing_page_view_{$companyId}_{$slug}", $ttl, function () use ($slug, $companyId) {
            return LandingPage::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', Status::Active->value)->with('product:id,company_id,title,slug,thumbnail,regular_price,discount,discount_type,type,short_description,warehouse_info,is_free_delivery')
                ->where('slug', $slug)->firstOrFail();
        });
        $product = $landing->product;
        abort_if($product === null, 404);

        $setup = $this->resolveSiteSetting($landing->company_id ?? $companyId);

        \Illuminate\Support\Facades\View::share('setup', $setup);
        \Illuminate\Support\Facades\View::share('isPreview', false);

        $products = $landing->products;

        return view('landing.landing' . $landing->template_id, compact('landing', 'product', 'products'));
    }

    /**
     * Resolve the active SiteSetting considering SuperAdmin vs Shop login.
     */
    protected function resolveSiteSetting(?int $companyId = null): \App\Models\SiteSetting
    {
        $user = auth()->user() ?? auth('sanctum')->user() ?? request()->user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || !empty($user->is_super_admin) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()));

        if ($user) {
            // When Super Admin is logged in, show Super Admin platform settings (company_id: null)
            // When Shop user is logged in, show their Shop's settings (company_id: $user->company_id)
            $targetCompanyId = $isSuperAdmin ? null : ($user->company_id ?: $companyId);
        } else {
            // Guest visitor: show the landing/shop company settings, fallback to current company
            $targetCompanyId = $companyId ?: ($this->company_id ?: (getCurrentCompany()->company_id ?? null));
        }

        $setup = null;
        if (!empty($targetCompanyId)) {
            $setup = \App\Models\SiteSetting::withoutGlobalScopes()
                ->where('company_id', $targetCompanyId)
                ->first();
        }

        // Fallback to super admin (platform) SiteSetting
        if (!$setup) {
            $setup = \App\Models\SiteSetting::withoutGlobalScopes()
                ->whereNull('company_id')
                ->first();
        }

        return $setup ?? new \App\Models\SiteSetting();
    }

    public function preview(Request $request, $template_id)
    {
        if ($request->isMethod('get')) {
            // When validation fails or redirects back via GET
            return response('
                <div style="font-family: sans-serif; text-align: center; padding: 50px; background: #fdfdfd; min-height: 100vh;">
                    <h3 style="color: #d9534f; margin-bottom: 15px;">Order Failed or Validation Error</h3>
                    <p style="color: #555; line-height: 1.5;">Could not process the order. Please ensure all fields are filled properly and the product is in stock.</p>
                    <button onclick="window.parent.postMessage(\'refresh_preview\', \'*\')" style="padding: 10px 20px; background: #13565e; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; font-weight: bold; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1">Refresh Preview</button>
                </div>
            ', 400);
        }

        $payload = json_decode($request->input('payload', '{}'), true);
        $form = $payload['form'] ?? [];
        $productData = $payload['product'] ?? [];
        $isPreview = $payload['is_preview'] ?? false;

        $landing = new \App\Models\LandingPage($form);
        if (isset($form['extras'])) {
            $landing->extras = is_string($form['extras']) ? json_decode($form['extras'], true) : $form['extras'];
        }
        $thumbPreview = $form['thumbnail']['previewUrl'] ?? (is_string($form['thumbnail'] ?? null) ? $form['thumbnail'] : null);
        $landing->thumbnail = is_string($thumbPreview) ? $thumbPreview : null;

        $videoPreview = $form['video']['previewUrl'] ?? (is_string($form['video'] ?? null) ? $form['video'] : null);
        $landing->video = is_string($videoPreview) ? $videoPreview : null;

        \Illuminate\Support\Facades\Log::info('Thumbnail/Video inside preview: thumb=' . print_r($landing->thumbnail, true) . ' video=' . print_r($landing->video, true));

        $product = null;
        if (isset($productData['id'])) {
            $product = \App\Models\Product::find($productData['id']);
        }
        if (!$product) {
            $product = new \App\Models\Product();
        }
        $product->fill($productData);
        if (isset($productData['id'])) {
            $product->id = $productData['id'];
        }

        $companyFromPayload = $productData['company_id'] ?? ($form['company_id'] ?? null);
        $setup = $this->resolveSiteSetting($companyFromPayload);

        \Illuminate\Support\Facades\View::share('setup', $setup);
        \Illuminate\Support\Facades\View::share('isPreview', (bool)$isPreview);

        $socialLinks = \App\Models\SocialSetting::where('status', 1)->get();
        \Illuminate\Support\Facades\View::share('socialLinks', $socialLinks);

        $landing->setRelation('product', $product);

        $multiProductIds = $form['product_ids'] ?? ($landing->extras['product_ids'] ?? []);
        if (!empty($multiProductIds) && is_array($multiProductIds)) {
            $products = \App\Models\Product::whereIn('id', $multiProductIds)->get();
        } else {
            $products = $product ? collect([$product]) : collect();
        }

        return view('landing.landing' . $template_id, compact('landing', 'product', 'products', 'isPreview'));
    }

    public function directPreview(Request $request, $template_id = 1)
    {
        $user = auth()->user() ?? auth('sanctum')->user() ?? request()->user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || !empty($user->is_super_admin) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()));

        $companyId = $this->company_id;
        if (!$companyId && $user && !$isSuperAdmin && $user->company_id) {
            $companyId = $user->company_id;
        }

        $product = Product::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('status', Status::Active->value)
            ->with(['variations'])
            ->first()
            ?? Product::with(['variations'])->first()
            ?? new Product([
                'title' => 'ডেমো প্রিমিয়াম প্রোডাক্ট',
                'regular_price' => 1200,
                'discount' => 350,
                'type' => 'single',
            ]);

        $landing = new LandingPage([
            'title' => $product->title ?? 'প্রিমিয়াম কোয়ালিটি অর্গানিক সজিনা পাতা গুঁড়া',
            'template_id' => (int)$template_id,
            'is_preview' => true,
            'thumbnail' => $product->thumbnail ?? null,
            'button_text' => 'অর্ডার করতে ক্লিক করুন',
            'phone_number' => '017XXXXXXXX',
            'regular_price' => '৪০০ গ্রাম সজিনা পাতার পাউডারের রেগুলার প্রাইস- ১৫০০/=',
            'discount_price' => '৪০০ গ্রাম সজিনা পাতার পাউডারের অফার প্রাইস- ১০৫০/= সীমিত সময়ের জন্য',
        ]);

        $setup = $this->resolveSiteSetting($companyId);

        \Illuminate\Support\Facades\View::share('setup', $setup);
        \Illuminate\Support\Facades\View::share('isPreview', true);

        $socialLinks = \App\Models\SocialSetting::where('status', 1)->get();
        \Illuminate\Support\Facades\View::share('socialLinks', $socialLinks);

        $landing->setRelation('product', $product);

        $demoProducts = Product::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('status', Status::Active->value)
            ->take(4)
            ->get();
        $products = $demoProducts->isNotEmpty() ? $demoProducts : collect([$product]);

        return view('landing.landing' . $template_id, [
            'landing' => $landing,
            'product' => $product,
            'products' => $products,
            'isPreview' => true,
        ]);
    }

    public function storeLandingOrder(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string',
            'product_id'      => 'required|exists:products,id',
            'landing_page_id' => 'nullable|exists:landing_pages,id',
            'qty'             => 'required|integer|min:1',
            'variation_id'    => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $customer = Party::updateOrCreate(
                ['phone' => $request->phone, 'company_id' => $this->company_id, 'type' => Party::TYPE_CUSTOMER],
                [
                    'name'     => $request->name,
                    'address'  => $request->address,
                    'password' => Hash::make('12345678'),
                    'status'   => Status::Active->value
                ]
            );

            // if (!auth('customer')->check()) {
            //     auth('customer')->login($customer);
            // }

            $unitPrice = 0;
            $warehouseId = null;
            $productId = $request->product_id;
            $rawVariationId = $request->variation_id ?: $request->variation_ids;
            $variationIds = [];
            if (is_array($rawVariationId)) {
                $variationIds = array_filter(array_map('trim', $rawVariationId));
            } elseif (is_string($rawVariationId) || is_numeric($rawVariationId)) {
                $variationIds = array_filter(explode(',', (string)$rawVariationId));
            }

            $orderItems = [];
            $warehouseInfoList = [];
            $primaryWarehouseId = null;
            $totalItemAmount = 0;
            $totalQuantity = 0;

            if (!empty($variationIds)) {
                $vQuantities = $request->variation_quantities ?? [];
                foreach ($variationIds as $vId) {
                    $variation = ProductVariation::with('product')->findOrFail($vId);
                    $product = $variation->product;
                    $vUnitPrice = $variation->regular_price - $variation->discount;
                    $itemQty = isset($vQuantities[$vId]) ? max(1, (int)$vQuantities[$vId]) : max(1, (int)($request->qty ?? 1));
                    $itemWarehouseId = null;

                    // Manual out of stock check
                    if ($variation->stock_status === 'out_of_stock') {
                        $stockMsg = 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    // If stock is managed, check quantity and warehouse stock
                    if ($product && $product->manage_stock) {
                        if ($variation->available_stock < $itemQty) {
                            $stockMsg = 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।';
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json(['success' => false, 'message' => $stockMsg], 422);
                            }
                            return back()->with('error', $stockMsg);
                        }

                        $vStock = ProductVariationStock::where('product_variation_id', $vId)
                            ->where('quantity', '>=', $itemQty)
                            ->first();
                        $itemWarehouseId = $vStock ? $vStock->warehouse_id : null;
                    } else {
                        $vStock = ProductVariationStock::where('product_variation_id', $vId)->first();
                        $itemWarehouseId = $vStock ? $vStock->warehouse_id : null;
                        if (!$itemWarehouseId) {
                            $itemWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                        }
                    }

                    if (!$itemWarehouseId) {
                        $stockMsg = 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = $itemWarehouseId;
                    }

                    $actualProductId = $variation->product_id ?? $productId;

                    $orderItems[] = [
                        'product_id'   => $actualProductId,
                        'variation_id' => $vId,
                        'quantity'     => $itemQty,
                        'unit_price'   => $vUnitPrice,
                    ];

                    $warehouseInfoList[] = [
                        'product_id'   => (int) $actualProductId,
                        'variation_id' => (int) $vId,
                        'warehouse_id' => (int) $itemWarehouseId,
                        'bin_id'       => null,
                        'quantity'     => (int) $itemQty,
                    ];

                    $totalItemAmount += ($itemQty * $vUnitPrice);
                    $totalQuantity += $itemQty;
                }
            }

            // Also check for multiple standalone products (e.g. from multi-product landing page)
            $rawProductIds = $request->product_ids;
            $multiProdIds = [];
            if (is_array($rawProductIds)) {
                $multiProdIds = array_filter(array_map('trim', $rawProductIds));
            } elseif (is_string($rawProductIds)) {
                $multiProdIds = array_filter(explode(',', $rawProductIds));
            }

            if (!empty($multiProdIds)) {
                $pQuantities = $request->product_quantities ?? [];
                foreach ($multiProdIds as $pId) {
                    $prodObj = Product::find($pId);
                    if (!$prodObj) continue;

                    $itemQty = isset($pQuantities[$pId]) ? max(1, (int)$pQuantities[$pId]) : max(1, (int)($request->qty ?? 1));
                    $unitPrice = $prodObj->sale_price ?? ($prodObj->regular_price - ($prodObj->discount ?? 0));

                    if ($prodObj->stock_status === 'out_of_stock') {
                        $stockMsg = 'দুঃখিত! ' . $prodObj->title . ' বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    $prodWarehouseId = null;
                    if ($prodObj->manage_stock) {
                        if ($prodObj->available_stock < $itemQty) {
                            $stockMsg = 'দুঃখিত! ' . $prodObj->title . ' পর্যাপ্ত স্টকে নেই।';
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json(['success' => false, 'message' => $stockMsg], 422);
                            }
                            return back()->with('error', $stockMsg);
                        }

                        $pWarehouseInfo = $prodObj->warehouse_info ?? [];
                        foreach ($pWarehouseInfo as $info) {
                            if (($info['quantity'] ?? 0) >= $itemQty) {
                                $prodWarehouseId = $info['warehouse_id'];
                                break;
                            }
                        }
                    } else {
                        $pWarehouseInfo = $prodObj->warehouse_info ?? [];
                        $prodWarehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                        if (!$prodWarehouseId) {
                            $prodWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                        }
                    }

                    if (!$prodWarehouseId) {
                        $prodWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }

                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = $prodWarehouseId;
                    }

                    $orderItems[] = [
                        'product_id'   => $pId,
                        'variation_id' => null,
                        'quantity'     => $itemQty,
                        'unit_price'   => $unitPrice,
                    ];

                    $warehouseInfoList[] = [
                        'product_id'   => (int) $pId,
                        'variation_id' => null,
                        'warehouse_id' => (int) $prodWarehouseId,
                        'bin_id'       => null,
                        'quantity'     => (int) $itemQty,
                    ];

                    $totalItemAmount += ($itemQty * $unitPrice);
                    $totalQuantity += $itemQty;
                }
            } elseif (empty($variationIds)) {
                $product = Product::findOrFail($productId);
                $unitPrice = $product->sale_price;
                $itemQty = (int)$request->qty;

                // Manual out of stock check
                if ($product->stock_status === 'out_of_stock') {
                    $stockMsg = 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $stockMsg], 422);
                    }
                    return back()->with('error', $stockMsg);
                }

                // If stock is managed, check quantity and warehouse stock
                if ($product->manage_stock) {
                    if ($product->available_stock < $itemQty) {
                        $stockMsg = 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    foreach ($pWarehouseInfo as $info) {
                        if (($info['quantity'] ?? 0) >= $itemQty) {
                            $primaryWarehouseId = $info['warehouse_id'];
                            break;
                        }
                    }
                } else {
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    $primaryWarehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }

                if (!$primaryWarehouseId) {
                    $stockMsg = 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $stockMsg], 422);
                    }
                    return back()->with('error', $stockMsg);
                }

                $orderItems[] = [
                    'product_id'   => $productId,
                    'variation_id' => null,
                    'quantity'     => $itemQty,
                    'unit_price'   => $unitPrice,
                ];

                $warehouseInfoList[] = [
                    'product_id'   => (int) $productId,
                    'variation_id' => null,
                    'warehouse_id' => (int) $primaryWarehouseId,
                    'bin_id'       => null,
                    'quantity'     => (int) $itemQty,
                ];

                $totalItemAmount = $itemQty * $unitPrice;
                $totalQuantity = $itemQty;
            }

            $settings = DB::table('site_settings')->where('company_id', $this->company_id)->first();
            $shippingCost = ($request->shipping_area == 'inside')
                ? ($settings->inside_charge ?? 60)
                : ($settings->outside_charge ?? 100);

            $orderProductIds = collect($orderItems)->pluck('product_id')->filter()->unique()->values();
            if ($orderProductIds->isNotEmpty()) {
                $hasNonFreeProduct = Product::whereIn('id', $orderProductIds)
                    ->where(function ($query) {
                        $query->whereNull('is_free_delivery')->orWhere('is_free_delivery', false);
                    })
                    ->exists();

                if (!$hasNonFreeProduct) {
                    $shippingCost = 0;
                }
            } else {
                $productForShipping = Product::find($productId);
                if ($productForShipping && $productForShipping->is_free_delivery) {
                    $shippingCost = 0;
                }
            }

            $orderData = [
                'type'             => Order::TYPE_LANDING,
                'company_id'       => $this->company_id,
                'warehouse_id'     => $primaryWarehouseId,
                'warehouse_info'   => $warehouseInfoList,
                'customer_id'      => $customer->id,
                'order_date'       => now(),
                'other_charges'    => $shippingCost,
                'shipping_address' => [
                    'name'    => $request->name,
                    'phone'   => $request->phone,
                    'address' => $request->address,
                ],
                'items'            => $orderItems,
                'status'           => Status::Pending->value,
            ];

            // Strict customer match: only update draft belonging to this specific customer
            $draftOrder = null;
            if ($customer) {
                $draftId = $request->draft_order_id ?: session()->get('current_landing_draft_order_id');
                if ($draftId) {
                    $draftOrder = Order::where('id', $draftId)
                        ->where('customer_id', $customer->id)
                        ->where('status', Status::Draft->value)
                        ->first();
                }
                if (!$draftOrder) {
                    $draftOrder = Order::where('customer_id', $customer->id)
                        ->where('type', Order::TYPE_LANDING)
                        ->where('status', Status::Draft->value)
                        ->latest()
                        ->first();
                }
            }

            if ($draftOrder) {
                // Confirm the existing partial/draft order
                $itemTotal = round((float)$totalItemAmount, 2);
                $grandTotal = round($itemTotal + (float)$shippingCost, 2);

                $draftOrder->orderDetails()->delete();
                foreach ($orderItems as $item) {
                    $lineTotal = round((int)$item['quantity'] * (float)$item['unit_price'], 2);
                    \App\Models\OrderDetail::create([
                        'order_id'      => $draftOrder->id,
                        'product_id'    => $item['product_id'],
                        'variation_id'  => $item['variation_id'],
                        'quantity'      => (int)$item['quantity'],
                        'unit_price'    => $item['unit_price'],
                        'discount'      => 0,
                        'tax_group_id'  => null,
                        'tax'           => 0,
                        'total'         => $lineTotal,
                    ]);
                }

                $draftOrder->update([
                    'warehouse_id'     => $primaryWarehouseId,
                    'warehouse_info'   => $warehouseInfoList,
                    'customer_id'      => $customer->id,
                    'order_date'       => now(),
                    'total_quantities' => (int) $totalQuantity,
                    'subtotal'         => $itemTotal,
                    'other_charges'    => $shippingCost,
                    'grand_total'      => $grandTotal,
                    'shipping_address' => [
                        'name'    => $request->name,
                        'phone'   => $request->phone,
                        'address' => $request->address,
                    ],
                    'status'           => Status::Pending->value,
                ]);

                // Deduct stock for the confirmed order
                $productService = app(\App\Services\ProductService::class);
                foreach ($warehouseInfoList as $wInfo) {
                    $itemProdId = $wInfo['product_id'] ?? $productId;
                    $itemProdObj = Product::find($itemProdId);
                    if ($itemProdObj && $itemProdObj->manage_stock) {
                        $stockData = [
                            'warehouse_id'     => $wInfo['warehouse_id'],
                            'bin_id'           => null,
                            'quantity'         => (int) $wInfo['quantity'],
                            'batch_number'     => null,
                            'serial_numbers'   => null,
                            'transaction_type' => 'sale',
                            'reference_type'   => 'LandingOrder',
                            'reference_id'     => $draftOrder->id,
                            'notes'            => "Stock deducted for order: {$draftOrder->order_no}"
                        ];
                        if (!empty($wInfo['variation_id'])) {
                            $stockData['variation_id'] = $wInfo['variation_id'];
                        }
                        $productService->removeStockFromWarehouse($itemProdId, $stockData);
                    }
                }

                $order = $draftOrder;
                session()->forget('current_landing_draft_order_id');
            } else {
                // If no draft order existed, create a new landing order directly
                $order = $this->orderService->createLandingOrder($orderData);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে!',
                    'order_id'      => $order->id,
                    'order_code'    => $order->order_no ?? ('#' . $order->id),
                    'thankyou_url'  => route('order.thankyou', $order->id),
                ]);
            }

            return redirect()->back()->with([
                'success'               => 'আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে!',
                'landing_order_success' => true,
                'order_id'              => $order->id,
                'order_code'            => $order->order_no ?? ('#' . $order->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Landing Order Error: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function partialSave(Request $request)
    {
        if (!$request->phone || strlen(trim($request->phone)) < 11) {
            return response()->json(['success' => false, 'message' => 'Invalid phone number']);
        }

        if (!$request->product_id) {
            return response()->json(['success' => false, 'message' => 'Product is required']);
        }

        try {
            DB::beginTransaction();

            $customer = Party::updateOrCreate(
                ['phone' => $request->phone, 'company_id' => $this->company_id, 'type' => Party::TYPE_CUSTOMER],
                [
                    'name'     => $request->name ?: 'Guest',
                    'address'  => $request->address ?: 'N/A',
                    'password' => Hash::make('12345678'),
                    'status'   => Status::Active->value
                ]
            );

            $productId = $request->product_id;
            $rawVariationId = $request->variation_id ?: $request->variation_ids;
            $variationIds = [];
            if (is_array($rawVariationId)) {
                $variationIds = array_filter(array_map('trim', $rawVariationId));
            } elseif (is_string($rawVariationId) || is_numeric($rawVariationId)) {
                $variationIds = array_filter(explode(',', (string)$rawVariationId));
            }

            $qty = max(1, (int) ($request->qty ?? 1));
            $orderItems = [];
            $warehouseInfoList = [];
            $primaryWarehouseId = null;
            $totalItemAmount = 0;
            $totalQuantity = 0;

            if (!empty($variationIds)) {
                $vQuantities = $request->variation_quantities ?? [];
                foreach ($variationIds as $vId) {
                    $variation = ProductVariation::with('product')->find($vId);
                    if (!$variation) {
                        return response()->json(['success' => false, 'message' => 'Variation not found'], 404);
                    }
                    $product = $variation->product;
                    $vUnitPrice = $variation->regular_price - $variation->discount;
                    $itemQty = isset($vQuantities[$vId]) ? max(1, (int)$vQuantities[$vId]) : $qty;
                    $itemWarehouseId = null;

                    // Manual out of stock check
                    if ($variation->stock_status === 'out_of_stock') {
                        return response()->json(['success' => false, 'message' => 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                    }

                    if ($product && $product->manage_stock) {
                        if ($variation->available_stock < $itemQty) {
                            return response()->json(['success' => false, 'message' => 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                        }

                        $vStock = ProductVariationStock::where('product_variation_id', $vId)
                            ->where('quantity', '>=', $itemQty)
                            ->first();
                        $itemWarehouseId = $vStock ? $vStock->warehouse_id : null;
                    } else {
                        $vStock = ProductVariationStock::where('product_variation_id', $vId)->first();
                        $itemWarehouseId = $vStock ? $vStock->warehouse_id : null;
                        if (!$itemWarehouseId) {
                            $itemWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                        }
                    }

                    if (!$itemWarehouseId) {
                        return response()->json(['success' => false, 'message' => 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                    }

                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = $itemWarehouseId;
                    }

                    $orderItems[] = [
                        'product_id'   => $product->id ?? $productId,
                        'variation_id' => $vId,
                        'quantity'     => $itemQty,
                        'unit_price'   => $vUnitPrice,
                    ];

                    $warehouseInfoList[] = [
                        'product_id'   => (int) ($product->id ?? $productId),
                        'variation_id' => (int) $vId,
                        'warehouse_id' => (int) $itemWarehouseId,
                        'bin_id'       => null,
                        'quantity'     => (int) $itemQty,
                    ];

                    $totalItemAmount += ($itemQty * $vUnitPrice);
                    $totalQuantity += $itemQty;
                }
            }

            // Also check for multiple standalone products in AJAX submission
            $rawProductIds = $request->product_ids;
            $multiProdIds = [];
            if (is_array($rawProductIds)) {
                $multiProdIds = array_filter(array_map('trim', $rawProductIds));
            } elseif (is_string($rawProductIds)) {
                $multiProdIds = array_filter(explode(',', $rawProductIds));
            }

            if (!empty($multiProdIds)) {
                $pQuantities = $request->product_quantities ?? [];
                foreach ($multiProdIds as $pId) {
                    $prodObj = Product::find($pId);
                    if (!$prodObj) continue;

                    $itemQty = isset($pQuantities[$pId]) ? max(1, (int)$pQuantities[$pId]) : max(1, (int)($request->qty ?? 1));
                    $unitPrice = $prodObj->sale_price ?? ($prodObj->regular_price - ($prodObj->discount ?? 0));

                    if ($prodObj->stock_status === 'out_of_stock') {
                        return response()->json(['success' => false, 'message' => 'দুঃখিত! ' . $prodObj->title . ' বর্তমানে স্টকে নেই।'], 422);
                    }

                    $prodWarehouseId = null;
                    if ($prodObj->manage_stock) {
                        if ($prodObj->available_stock < $itemQty) {
                            return response()->json(['success' => false, 'message' => 'দুঃখিত! ' . $prodObj->title . ' পর্যাপ্ত স্টকে নেই।'], 422);
                        }

                        $pWarehouseInfo = $prodObj->warehouse_info ?? [];
                        foreach ($pWarehouseInfo as $info) {
                            if (($info['quantity'] ?? 0) >= $itemQty) {
                                $prodWarehouseId = $info['warehouse_id'];
                                break;
                            }
                        }
                    } else {
                        $pWarehouseInfo = $prodObj->warehouse_info ?? [];
                        $prodWarehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                        if (!$prodWarehouseId) {
                            $prodWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                        }
                    }

                    if (!$prodWarehouseId) {
                        $prodWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }

                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = $prodWarehouseId;
                    }

                    $orderItems[] = [
                        'product_id'   => $pId,
                        'variation_id' => null,
                        'quantity'     => (int) $itemQty,
                        'unit_price'   => $unitPrice,
                    ];

                    $warehouseInfoList[] = [
                        'product_id'   => (int) $pId,
                        'variation_id' => null,
                        'warehouse_id' => (int) $prodWarehouseId,
                        'bin_id'       => null,
                        'quantity'     => (int) $itemQty,
                    ];

                    $totalItemAmount += ($itemQty * $unitPrice);
                    $totalQuantity += $itemQty;
                }
            } elseif (empty($variationIds)) {
                $product = Product::find($productId);
                if (!$product) {
                    return response()->json(['success' => false, 'message' => 'Product not found'], 404);
                }
                $unitPrice = $product->sale_price;

                // Manual out of stock check
                if ($product->stock_status === 'out_of_stock') {
                    return response()->json(['success' => false, 'message' => 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                }

                if ($product->manage_stock) {
                    if ($product->available_stock < $qty) {
                        return response()->json(['success' => false, 'message' => 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                    }

                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    foreach ($pWarehouseInfo as $info) {
                        if (($info['quantity'] ?? 0) >= $qty) {
                            $primaryWarehouseId = $info['warehouse_id'];
                            break;
                        }
                    }
                } else {
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    $primaryWarehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                    if (!$primaryWarehouseId) {
                        $primaryWarehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }

                if (!$primaryWarehouseId) {
                    return response()->json(['success' => false, 'message' => 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                }

                $orderItems[] = [
                    'product_id'   => $productId,
                    'variation_id' => null,
                    'quantity'     => (int) $qty,
                    'unit_price'   => $unitPrice,
                ];

                $warehouseInfoList[] = [
                    'product_id'   => (int) $productId,
                    'variation_id' => null,
                    'warehouse_id' => (int) $primaryWarehouseId,
                    'bin_id'       => null,
                    'quantity'     => (int) $qty,
                ];

                $totalItemAmount = $qty * $unitPrice;
                $totalQuantity = $qty;
            }

            $settings = DB::table('site_settings')->where('company_id', $this->company_id)->first();
            $shippingCost = ($request->shipping_area == 'inside')
                ? ($settings->inside_charge ?? 60)
                : ($settings->outside_charge ?? 100);

            $orderProductIds = collect($orderItems)->pluck('product_id')->filter()->unique()->values();
            if ($orderProductIds->isNotEmpty()) {
                $hasNonFreeProduct = Product::whereIn('id', $orderProductIds)
                    ->where(function ($query) {
                        $query->whereNull('is_free_delivery')->orWhere('is_free_delivery', false);
                    })
                    ->exists();

                if (!$hasNonFreeProduct) {
                    $shippingCost = 0;
                }
            } else {
                $productForShipping = Product::find($productId);
                if ($productForShipping && $productForShipping->is_free_delivery) {
                    $shippingCost = 0;
                }
            }

            // Strict customer match: only find and update draft belonging to THIS customer
            $draftOrder = null;
            $draftId = $request->draft_order_id ?: session()->get('current_landing_draft_order_id');
            if ($draftId) {
                $draftOrder = Order::where('id', $draftId)
                    ->where('customer_id', $customer->id)
                    ->where('status', Status::Draft->value)
                    ->first();
            }
            if (!$draftOrder) {
                $draftOrder = Order::where('customer_id', $customer->id)
                    ->where('type', Order::TYPE_LANDING)
                    ->where('status', Status::Draft->value)
                    ->latest()
                    ->first();
            }

            $itemTotal = round((float)$totalItemAmount, 2);
            $grandTotal = round($itemTotal + (float)$shippingCost, 2);

            if ($draftOrder) {
                // UPDATE existing draft - NO NEW ORDER CREATED!
                $draftOrder->orderDetails()->delete();
                foreach ($orderItems as $item) {
                    $lineTotal = round((int)$item['quantity'] * (float)$item['unit_price'], 2);
                    \App\Models\OrderDetail::create([
                        'order_id'      => $draftOrder->id,
                        'product_id'    => $item['product_id'],
                        'variation_id'  => $item['variation_id'],
                        'quantity'      => (int)$item['quantity'],
                        'unit_price'    => $item['unit_price'],
                        'discount'      => 0,
                        'tax_group_id'  => null,
                        'tax'           => 0,
                        'total'         => $lineTotal,
                    ]);
                }

                $draftOrder->update([
                    'warehouse_id'     => $primaryWarehouseId,
                    'warehouse_info'   => $warehouseInfoList,
                    'customer_id'      => $customer->id,
                    'order_date'       => now(),
                    'total_quantities' => (int) $totalQuantity,
                    'subtotal'         => $itemTotal,
                    'other_charges'    => $shippingCost,
                    'grand_total'      => $grandTotal,
                    'shipping_address' => [
                        'name'    => $request->name ?: $customer->name,
                        'phone'   => $request->phone,
                        'address' => $request->address ?: 'N/A',
                    ],
                    'status'           => Status::Draft->value,
                ]);

                $order = $draftOrder;
            } else {
                // Only create if NO draft exists yet for this customer
                $orderData = [
                    'type'             => Order::TYPE_LANDING,
                    'company_id'       => $this->company_id,
                    'warehouse_id'     => $primaryWarehouseId,
                    'warehouse_info'   => $warehouseInfoList,
                    'customer_id'      => $customer->id,
                    'order_date'       => now(),
                    'other_charges'    => $shippingCost,
                    'shipping_address' => [
                        'name'    => $request->name ?: $customer->name,
                        'phone'   => $request->phone,
                        'address' => $request->address ?: 'N/A',
                    ],
                    'items'            => $orderItems,
                    'status'           => Status::Draft->value,
                ];

                $order = $this->orderService->createLandingOrder($orderData);
            }

            session()->put('current_landing_draft_order_id', $order->id);

            DB::commit();
            return response()->json(['success' => true, 'order_id' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Landing Partial Order Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

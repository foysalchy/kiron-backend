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
        $ttl = now()->addHours(6);
        $landing = Cache::remember("landing_page_view_{$companyId}_{$slug}", $ttl, function () use ($slug, $companyId) {
            return LandingPage::where('company_id', $companyId)
                ->where('status', Status::Active->value)->with('product:id,company_id,title,slug,thumbnail,regular_price,discount,discount_type,type,short_description,warehouse_info,is_free_delivery')
                ->where('slug', $slug)->firstOrFail();
        });
        $product = $landing->product;
        abort_if($product === null, 404);

        $setup = \App\Models\SiteSetting::where('company_id', $landing->company_id)
            ->orWhereNull('company_id')
            ->orderByRaw('company_id IS NULL ASC')
            ->first() ?? new \App\Models\SiteSetting();

        \Illuminate\Support\Facades\View::share('setup', $setup);
        \Illuminate\Support\Facades\View::share('isPreview', false);

        return view('landing.landing' . $landing->template_id, compact('landing', 'product'));
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
        if (isset($form['thumbnail']['previewUrl'])) {
            $landing->thumbnail = $form['thumbnail']['previewUrl'];
        }

        \Illuminate\Support\Facades\Log::info('Thumbnail inside preview: ' . print_r($landing->thumbnail, true));

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

        $setup = \App\Models\SiteSetting::where('company_id', $this->company_id)
            ->orWhereNull('company_id')
            ->orderByRaw('company_id IS NULL ASC')
            ->first() ?? new \App\Models\SiteSetting();

        \Illuminate\Support\Facades\View::share('setup', $setup);
        \Illuminate\Support\Facades\View::share('isPreview', (bool)$isPreview);

        $socialLinks = \App\Models\SocialSetting::where('status', 1)->get();
        \Illuminate\Support\Facades\View::share('socialLinks', $socialLinks);

        $landing->setRelation('product', $product);

        return view('landing.landing' . $template_id, compact('landing', 'product', 'isPreview'));
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
            $variationId = $request->variation_id;

            if ($variationId) {
                $variation = ProductVariation::with('product')->findOrFail($variationId);
                $product = $variation->product;
                $unitPrice = $variation->regular_price - $variation->discount;

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
                    if ($variation->available_stock < $request->qty) {
                        $stockMsg = 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)
                        ->where('quantity', '>=', $request->qty)
                        ->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                } else {
                    // unmanaged: pick assigned warehouse if any, or company warehouse fallback
                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                    if (!$warehouseId) {
                        $warehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }
            } else {
                $product = Product::findOrFail($productId);
                $unitPrice = $product->sale_price;

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
                    if ($product->available_stock < $request->qty) {
                        $stockMsg = 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $stockMsg], 422);
                        }
                        return back()->with('error', $stockMsg);
                    }

                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    foreach ($pWarehouseInfo as $info) {
                        if (($info['quantity'] ?? 0) >= $request->qty) {
                            $warehouseId = $info['warehouse_id'];
                            break;
                        }
                    }
                } else {
                    // unmanaged: pick assigned warehouse if any, or company warehouse fallback
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    $warehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                    if (!$warehouseId) {
                        $warehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }
            }

            if (!$warehouseId) {
                $stockMsg = 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $stockMsg], 422);
                }
                return back()->with('error', $stockMsg);
            }

            $settings = DB::table('site_settings')->where('company_id', $this->company_id)->first();
            $shippingCost = ($request->shipping_area == 'inside')
                ? ($settings->inside_charge ?? 60)
                : ($settings->outside_charge ?? 100);

            $productForShipping = Product::find($productId);
            if ($productForShipping && $productForShipping->is_free_delivery) {
                $shippingCost = 0;
            }

            $orderData = [
                'type'             => Order::TYPE_LANDING,
                'company_id'       => $this->company_id,
                'warehouse_id'     => $warehouseId,
                'warehouse_info'   => [[
                    'product_id'   => (int) $productId,
                    'variation_id' => $variationId ? (int)$variationId : null,
                    'warehouse_id' => (int) $warehouseId,
                    'bin_id'       => null,
                    'quantity'     => (int) $request->qty,
                ]],
                'customer_id'      => $customer->id,
                'order_date'       => now(),
                'other_charges'    => $shippingCost,
                'shipping_address' => [
                    'name'    => $request->name,
                    'phone'   => $request->phone,
                    'address' => $request->address,
                ],
                'items' => [
                    [
                        'product_id'   => $productId,
                        'variation_id' => $variationId,
                        'quantity'     => $request->qty,
                        'unit_price'   => $unitPrice,
                    ]
                ],
                'status' => Status::Pending->value,
            ];

            $draftId = session()->get('current_landing_draft_order_id');
            $draftOrder = null;
            if ($draftId) {
                $draftOrder = Order::where('id', $draftId)->where('status', Status::Draft->value)->first();
            }
            if (!$draftOrder && $customer) {
                $draftOrder = Order::where('company_id', $this->company_id)
                    ->where('customer_id', $customer->id)
                    ->where('status', Status::Draft->value)
                    ->latest()
                    ->first();
            }

            if ($draftOrder) {
                // Confirm the existing partial/draft order
                $itemTotal = round((int)$request->qty * (float)$unitPrice, 2);
                $grandTotal = round($itemTotal + (float)$shippingCost, 2);

                $draftOrder->orderDetails()->delete();
                \App\Models\OrderDetail::create([
                    'order_id'      => $draftOrder->id,
                    'product_id'    => $productId,
                    'variation_id'  => $variationId,
                    'quantity'      => (int)$request->qty,
                    'unit_price'    => $unitPrice,
                    'discount'      => 0,
                    'tax_group_id'  => null,
                    'tax'           => 0,
                    'total'         => $itemTotal,
                ]);

                $draftOrder->update([
                    'warehouse_id'     => $warehouseId,
                    'warehouse_info'   => [[
                        'product_id'   => (int) $productId,
                        'variation_id' => $variationId ? (int)$variationId : null,
                        'warehouse_id' => (int) $warehouseId,
                        'bin_id'       => null,
                        'quantity'     => (int) $request->qty,
                    ]],
                    'customer_id'      => $customer->id,
                    'order_date'       => now(),
                    'total_quantities' => (int) $request->qty,
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
                $productObj = Product::find($productId);
                if ($productObj && $productObj->manage_stock) {
                    $stockData = [
                        'warehouse_id'     => $warehouseId,
                        'bin_id'           => null,
                        'quantity'         => (int) $request->qty,
                        'batch_number'     => null,
                        'serial_numbers'   => null,
                        'transaction_type' => 'sale',
                        'reference_type'   => 'LandingOrder',
                        'reference_id'     => $draftOrder->id,
                        'notes'            => "Stock deducted for order: {$draftOrder->order_no}"
                    ];
                    if ($variationId) {
                        $stockData['variation_id'] = $variationId;
                    }
                    $productService->removeStockFromWarehouse($productId, $stockData);
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

            $unitPrice = 0;
            $warehouseId = null;
            $productId = $request->product_id;
            $variationId = $request->variation_id;

            $qty = max(1, (int) ($request->qty ?? 1));

            if ($variationId) {
                $variation = ProductVariation::with('product')->find($variationId);
                if (!$variation) {
                    return response()->json(['success' => false, 'message' => 'Variation not found'], 404);
                }
                $product = $variation->product;
                $unitPrice = $variation->regular_price - $variation->discount;

                // Manual out of stock check
                if ($variation->stock_status === 'out_of_stock') {
                    return response()->json(['success' => false, 'message' => 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                }

                if ($product && $product->manage_stock) {
                    if ($variation->available_stock < $qty) {
                        return response()->json(['success' => false, 'message' => 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।'], 422);
                    }

                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)
                        ->where('quantity', '>=', $qty)
                        ->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                } else {
                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                    if (!$warehouseId) {
                        $warehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }
            } else {
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
                            $warehouseId = $info['warehouse_id'];
                            break;
                        }
                    }
                } else {
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    $warehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                    if (!$warehouseId) {
                        $warehouseId = DB::table('warehouses')->where('company_id', $this->company_id)->value('id');
                    }
                }
            }

            if (!$warehouseId) {
                return response()->json(['success' => false, 'message' => 'দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।'], 422);
            }

            $settings = DB::table('site_settings')->where('company_id', $this->company_id)->first();
            $shippingCost = ($request->shipping_area == 'inside')
                ? ($settings->inside_charge ?? 60)
                : ($settings->outside_charge ?? 100);

            $productForShipping = Product::find($productId);
            if ($productForShipping && $productForShipping->is_free_delivery) {
                $shippingCost = 0;
            }

            // Remove previous draft if any
            $oldDraftId = session()->get('current_landing_draft_order_id');
            if ($oldDraftId) {
                $oldDraft = Order::where('id', $oldDraftId)->where('status', Status::Draft->value)->first();
                if ($oldDraft) {
                    $oldDraft->orderDetails()->delete();
                    $oldDraft->delete();
                }
            }

            $orderData = [
                'type'             => Order::TYPE_LANDING,
                'company_id'       => $this->company_id,
                'warehouse_id'     => $warehouseId,
                'warehouse_info'   => [[
                    'product_id'   => (int) $productId,
                    'variation_id' => $variationId ? (int)$variationId : null,
                    'warehouse_id' => (int) $warehouseId,
                    'bin_id'       => null,
                    'quantity'     => (int) ($request->qty ?? 1),
                ]],
                'customer_id'      => $customer->id,
                'order_date'       => now(),
                'other_charges'    => $shippingCost,
                'shipping_address' => [
                    'name'    => $request->name ?: $customer->name,
                    'phone'   => $request->phone,
                    'address' => $request->address ?: 'N/A',
                ],
                'items' => [
                    [
                        'product_id'   => $productId,
                        'variation_id' => $variationId,
                        'quantity'     => (int) ($request->qty ?? 1),
                        'unit_price'   => $unitPrice,
                    ]
                ],
                'status' => Status::Draft->value,
            ];

            $order = $this->orderService->createLandingOrder($orderData);
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

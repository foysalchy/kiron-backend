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
                ->where('status', Status::Active->value)->with('product:id,company_id,title,slug,thumbnail,regular_price,discount,discount_type,type,warehouse_info')
                ->where('slug', $slug)->firstOrFail();
        });
        $product = $landing->product;
        abort_if($product === null, 404);

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

        $landing = new \App\Models\LandingPage($form);
        if (isset($form['extras'])) {
            $landing->extras = is_string($form['extras']) ? json_decode($form['extras'], true) : $form['extras'];
        }
        if (isset($form['thumbnail']['previewUrl'])) {
            $landing->thumbnail = $form['thumbnail']['previewUrl'];
        }

        \Illuminate\Support\Facades\Log::info('Thumbnail inside preview: ' . print_r($landing->thumbnail, true));

        $product = new \App\Models\Product($productData);
        if (isset($productData['id'])) {
            $product->id = $productData['id'];
        }

        $setup = \App\Models\SiteSetting::where('company_id', $this->company_id)
            ->orWhereNull('company_id')
            ->orderByRaw('company_id IS NULL ASC')
            ->first() ?? new \App\Models\SiteSetting();
            
        \Illuminate\Support\Facades\View::share('setup', $setup);

        $socialLinks = \App\Models\SocialSetting::where('status', 1)->get();
        \Illuminate\Support\Facades\View::share('socialLinks', $socialLinks);
        
        $landing->setRelation('product', $product);

        return view('landing.landing' . $template_id, compact('landing', 'product'));
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
                $unitPrice = $variation->regular_price - $variation->discount;

                if ($variation->product->manage_stock) {                          // ← guard
                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)
                        ->where('quantity', '>=', $request->qty)
                        ->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                } else {
                    // unmanaged হলে quantity check ছাড়াই যেকোনো assigned warehouse নাও
                    $vStock = ProductVariationStock::where('product_variation_id', $variationId)->first();
                    $warehouseId = $vStock ? $vStock->warehouse_id : null;
                }
            } else {
                $product = Product::findOrFail($productId);
                $unitPrice = $product->sale_price;

                if ($product->manage_stock) {                                    
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    foreach ($pWarehouseInfo as $info) {
                        if ($info['quantity'] >= $request->qty) {
                            $warehouseId = $info['warehouse_id'];
                            break;
                        }
                    }
                } else {
                    $pWarehouseInfo = $product->warehouse_info ?? [];
                    $warehouseId = $pWarehouseInfo[0]['warehouse_id'] ?? null;
                }
            }

            if (!$warehouseId) {
                return back()->with('error', 'Sorry, This product is not available now!');
            }

            $settings = DB::table('site_settings')->where('company_id', $this->company_id)->first();
            $shippingCost = ($request->shipping_area == 'inside')
                ? ($settings->inside_charge ?? 60)
                : ($settings->outside_charge ?? 100);

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

            $order = $this->orderService->createLandingOrder($orderData);

            DB::commit();
            return redirect()->route('order.thankyou', $order->id)->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Landing Order Error: " . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}

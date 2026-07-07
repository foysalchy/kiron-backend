<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
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
        $landing = LandingPage::with('product')->where('slug', $slug)->firstOrFail();

        $product = $landing->product;

        return view('landing.landing' . $landing->template_id, compact('landing', 'product'));
    }
    public function storeLandingOrder(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string',
            'product_id'      => 'required|exists:products,id',
            'landing_page_id' => 'required|exists:landing_pages,id',
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

            if (!auth('customer')->check()) {
                auth('customer')->login($customer);
            }

            $unitPrice = 0;
            $warehouseId = null;
            $productId = $request->product_id;
            $variationId = $request->variation_id;

            if ($variationId) {
                $variation = \App\Models\ProductVariation::findOrFail($variationId);
                $unitPrice = $variation->regular_price - $variation->discount;

                $vStock = \App\Models\ProductVariationStock::where('product_variation_id', $variationId)
                    ->where('quantity', '>=', $request->qty)
                    ->first();
                $warehouseId = $vStock ? $vStock->warehouse_id : null;
            } else {
                $product = \App\Models\Product::findOrFail($productId);
                $unitPrice = $product->sale_price;

                $pWarehouseInfo = $product->warehouse_info ?? [];
                foreach ($pWarehouseInfo as $info) {
                    if ($info['quantity'] >= $request->qty) {
                        $warehouseId = $info['warehouse_id'];
                        break;
                    }
                }
            }

            if (!$warehouseId) {
                return back()->with('error', 'দুঃখিত, এই প্রোডাক্টটি বর্তমানে পর্যাপ্ত স্টকে নেই।');
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
            return redirect()->route('order.invoice', $order->id)->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Landing Order Error: " . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}

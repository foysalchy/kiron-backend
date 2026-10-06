<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Cart as CartTrack;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\CouponService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Models\Cart as CartModel;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CartController extends FrontendController
{
    public function __construct(protected CouponService $couponService)
    {
        parent::__construct();
    }
    public function index()
    {
        $companyId = $this->company_id;

        $cartContent = Cart::content();
        $subtotal = (float) str_replace(',', '', Cart::subtotal());

        // Delivery charge – fetched from cache
        $settings = \Illuminate\Support\Facades\Cache::remember("site_settings_{$companyId}", 86400, function () use ($companyId) {
            return SiteSetting::where('company_id', $companyId)->first();
        });
        $defaultInside = $settings->inside_charge ?? 60;

        $shipping = session()->get('shipping_cost', $defaultInside);
        $shipping_area = session()->get('shipping_area', 'inside');
        //coupon discount
        $discount = 0;

        if (session()->has('coupon')) {
            try {
                $couponSession = session()->get('coupon');
                $customerId = auth('customer')->id();

                if (!$customerId && session()->has('current_draft_order_id')) {
                    $draftOrder = \App\Models\Order::find(session()->get('current_draft_order_id'));
                    if ($draftOrder && $draftOrder->customer_id) {
                        $customerId = $draftOrder->customer_id;
                    }
                }

                $result = $this->couponService->validateCoupon(
                    $couponSession['coupon_code'],
                    $subtotal,
                    $customerId
                );

                $discount = $result['discount_amount'];
            } catch (\Exception $e) {
                session()->forget('coupon');
            }
        }

        $total = ($subtotal - $discount) + $shipping;

        $productIds = [];
        foreach ($cartContent as $item) {
            $productIds[] = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
        }

        $cacheKey = "cart_related_" . md5(implode('_', $productIds));
        $relatedProducts = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($productIds) {
            $megaCategoryIds = [];
            if (!empty($productIds)) {
                $productsInCart = Product::whereIn('id', $productIds)->select('id', 'mega_category_ids')->get();
                foreach ($productsInCart as $p) {
                    if ($p->mega_category_ids && is_array($p->mega_category_ids)) {
                        $megaCategoryIds = array_merge($megaCategoryIds, $p->mega_category_ids);
                    }
                }
            }
            $megaCategoryIds = array_unique($megaCategoryIds);

            $relatedQuery = Product::where('status', Status::Active->value)
                ->whereNotIn('id', $productIds);

            if (!empty($megaCategoryIds)) {
                $relatedQuery->where(function ($q) use ($megaCategoryIds) {
                    foreach ($megaCategoryIds as $catId) {
                        $q->orWhereJsonContains('mega_category_ids', $catId);
                    }
                });
            }

            $products = $relatedQuery->inRandomOrder()->limit(8)->get();
            if ($products->isEmpty()) {
                $products = Product::where('status', Status::Active->value)->latest()->limit(8)->get();
            }
            return $products;
        });


        return $this->view(
            'frontend.cart',
            compact(
                'cartContent',
                'subtotal',
                'discount',
                'shipping',
                'total',
                'shipping_area',
                'relatedProducts'
            )
        );
    }
    //shipping area method
    public function updateShipping(Request $request)
    {
        $companyId = $this->company_id;
        // Delivery charge – always fetch fresh from DB (no cache)
        $settings = SiteSetting::where('company_id', $companyId)->first();
        $inside  = $settings->inside_charge ?? 60;
        $outside = $settings->outside_charge ?? 100;

        $cost = ($request->area == 'outside') ? $outside : $inside;

        session()->put('shipping_area', $request->area);
        session()->put('shipping_cost', $cost);

        if ($request->ajax() || $request->wantsJson()) {
            $subtotal = (float) str_replace(',', '', Cart::subtotal());

            //
            $discount = 0;
            if (session()->has('coupon')) {
                try {
                    $couponSession = session()->get('coupon');
                    $customerId = auth('customer')->id();

                    if (!$customerId && session()->has('current_draft_order_id')) {
                        $draftOrder = \App\Models\Order::find(session()->get('current_draft_order_id'));
                        if ($draftOrder && $draftOrder->customer_id) {
                            $customerId = $draftOrder->customer_id;
                        }
                    }

                    $result = $this->couponService->validateCoupon(
                        $couponSession['coupon_code'],
                        $subtotal,
                        $customerId
                    );
                    $discount = $result['discount_amount'];
                } catch (\Exception $e) {
                    session()->forget('coupon');
                }
            }

            $total = ($subtotal - $discount) + $cost;

            return response()->json([
                'success' => true,
                'shipping_cost' => $cost,
                'grand_total' => number_format($total),
                'message' => 'Delivery charge has been updated.'
            ]);
        }

        return back()->with('success', 'Shipping area has been updated.');
    }

    public function applyCoupon(Request $request)
    {
        try {
            $subtotal = (float) str_replace(',', '', Cart::subtotal());
            $customerId = auth('customer')->id();

            // Try to get customer ID from draft order if not logged in
            if (!$customerId && session()->has('current_draft_order_id')) {
                $draftOrder = \App\Models\Order::find(session()->get('current_draft_order_id'));
                if ($draftOrder && $draftOrder->customer_id) {
                    $customerId = $draftOrder->customer_id;
                }
            }

            $result = $this->couponService->validateCoupon(
                $request->coupon_code,
                $subtotal,
                $customerId
            );

            session()->put('coupon', $result);

            if ($request->ajax() || $request->wantsJson()) {
                $discount = $result['discount_amount'];
                $settings = \App\Models\SiteSetting::where('company_id', $this->company_id)->first();
                $shipping = session()->get('shipping_cost', $settings->inside_charge ?? 60);
                $total = ($subtotal - $discount) + $shipping;

                return response()->json([
                    'success' => true,
                    'discount_amount' => $discount,
                    'grand_total' => number_format($total),
                    'coupon_code' => $request->coupon_code,
                    'message' => 'Congratulations! The coupon has been applied successfully.'
                ]);
            }

            return back()->with('success', 'Congratulations! The coupon has been applied successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function removeCoupon(Request $request)
    {
        session()->forget('coupon');
        if ($request->ajax() || $request->wantsJson()) {
            $subtotal = (float) str_replace(',', '', \Gloudemans\Shoppingcart\Facades\Cart::subtotal());
            $settings = \App\Models\SiteSetting::where('company_id', $this->company_id)->first();
            $shipping = session()->get('shipping_cost', $settings->inside_charge ?? 60);
            $total = $subtotal + $shipping;

            return response()->json([
                'success' => true,
                'grand_total' => number_format($total),
                'message' => 'The coupon has been removed.'
            ]);
        }

        return back()->with('success', 'The coupon has been removed.');
    }
    // product add to cart
    public function add(Request $request)
    {
        try {
            //
            $items = $request->items;
            if (empty($items)) {
                return response()->json(['status' => 'error', 'message' => 'No items selected.'], 422);
            }

            foreach ($items as $item) {
                //
                $qty         = (int) ($item['qty'] ?? 1);
                $productId   = null;
                $variationId = null;
                $warehouseId = null;
                $binId       = null;

                if (isset($item['variation_id'])) {
                    // ── VARIATION PRODUCT  ──
                    $variation = ProductVariation::with(['product:id,title,slug,thumbnail', 'stocks.warehouse'])->findOrFail($item['variation_id']);
                    $stockRecord = $variation->stocks->where('quantity', '>=', $qty)->sortByDesc('quantity')->first();

                    if ($variation->product->manage_stock) {
                        $stockRecord = $variation->stocks->where('quantity', '>=', $qty)->sortByDesc('quantity')->first();

                        if (!$stockRecord) continue;

                        $warehouseId = $stockRecord->warehouse_id;
                        $binId       = $stockRecord->bin_id;
                    } else {
                        $stockRecord = $variation->stocks->first();
                        $warehouseId = $stockRecord->warehouse_id ?? null;
                        $binId       = $stockRecord->bin_id ?? null;
                    }

                    $productId   = $variation->product_id;
                    $variationId = $variation->id;

                    Cart::add([
                        'id'      => 'var_' . $variation->id,
                        'name'    => $variation->product->title,
                        'qty'     => $qty,
                        'price'   => $variation->final_price,
                        'weight'  => 0,
                        'options' => [
                            'slug'          => $variation->product->slug,
                            'variation_id'  => $variation->id,
                            'thumbnail'     => $variation->image_url ?? $variation->product->thumbnail_95_url ?? $variation->product->thumbnail_url,
                            'variant'       => $variation->display_name,
                            'regular_price' => $variation->regular_price,
                            'warehouse_id'  => $warehouseId,
                            'bin_id'        => $binId ?? null,
                        ],
                    ]);
                } else {
                    // ── SINGLE PRODUCT  ──
                    $product = Product::findOrFail($item['id']);
                    $bestWarehouse = null;
                    $bestBinId = null;
                    if ($product->manage_stock) {                      // ← guard
                        $bestQty = 0;

                        if (!empty($product->warehouse_info)) {
                            foreach ($product->warehouse_info as $info) {
                                $q = (int) ($info['quantity'] ?? 0);
                                $wId = (int) ($info['warehouse_id'] ?? 0);
                                if ($wId > 0 && $q > $bestQty) {
                                    $bestQty = $q;
                                    $bestWarehouse = $wId;
                                    $bestBinId = $info['bin_id'] ?? null;
                                }
                            }
                        }

                        if ($bestQty < $qty) continue;
                    } else {
                        if (!empty($product->warehouse_info)) {
                            $firstWarehouse = $product->warehouse_info[0] ?? null;
                            $bestWarehouse = $firstWarehouse['warehouse_id'] ?? null;
                            $bestBinId = $firstWarehouse['bin_id'] ?? null;
                        }
                    }


                    $productId   = $product->id;
                    $warehouseId = $bestWarehouse;
                    $binId       = $bestBinId;

                    Cart::add([
                        'id'      => $product->id,
                        'name'    => $product->title,
                        'qty'     => $qty,
                        'price'   => $product->sale_price,
                        'weight'  => 0,
                        'options' => [
                            'slug'          => $product->slug,
                            'thumbnail'     => $product->thumbnail_95_url ?? $product->thumbnail_url,
                            'regular_price' => $product->regular_price,
                            'warehouse_id'  => $warehouseId,
                            'bin_id'        => $binId ?? null,
                        ],
                    ]);
                }

                // ── COMPANY ID & TRACKING  ──
                $customerId = auth('customer')->check() ? auth('customer')->id() : null;
                $finalCompanyId = $this->company_id;
                if (!$finalCompanyId) {
                    if (auth('customer')->check()) {
                        $finalCompanyId = auth('customer')->user()->company_id;
                    } else {
                        $productForId = Product::find($productId);
                        $finalCompanyId = $productForId ? $productForId->company_id : null;
                    }
                }

                if ($finalCompanyId) {
                    CartTrack::updateOrCreate(
                        ['session_id' => session()->getId(), 'product_id' => $productId, 'variation_id' => $variationId],
                        ['company_id' => $finalCompanyId, 'customer_id' => $customerId, 'quantity' => $qty, 'status' => CartTrack::ADDED]
                    );
                }
            }
            return response()->json(['status' => 'success', 'cart_count' => \Gloudemans\Shoppingcart\Facades\Cart::count(), 'message' => 'Successfully added to cart!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    // public function add( Request $request)
    // {
    //     try {
    //         $qty         = (int) ($request->qty ?? 1);
    //         $productId   = null;
    //         $variationId = null;
    //         $warehouseId = null;
    //         $binId       = null;

    //         if ($request->filled('variation_id')) {

    //             // ── VARIATION PRODUCT ──
    //             $variation = ProductVariation::with(['product', 'stocks.warehouse'])->findOrFail($request->variation_id);

    //             //  warehouse-
    //             $stockRecord = $variation->stocks
    //                 ->where('quantity', '>=', $qty)
    //                 ->sortByDesc('quantity') //stock
    //                 ->first();

    //             if (!$stockRecord) {
    //                 $alreadyInCart = $this->getCartQty(null, $variation->id);
    //                 $msg = $alreadyInCart > 0
    //                     ? "Only cart qty available. (Already {$alreadyInCart} in cart)"
    //                     : 'Sorry, this variation is currently out of stock.';
    //                 return response()->json(['status' => 'error', 'message' => $msg], 422);
    //             }

    //             // Cart-এ already থাকা qty বাদ দিয়ে check
    //             $alreadyInCart    = $this->getCartQty(null, $variation->id);
    //             $availableForCart = $stockRecord->quantity - $alreadyInCart;

    //             if ($availableForCart < $qty) {
    //                 $msg = $alreadyInCart > 0
    //                     ? "Only {$availableForCart} more can be added. (Already {$alreadyInCart} in cart)"
    //                     : 'Sorry, this variation is currently out of stock.';
    //                 return response()->json(['status' => 'error', 'message' => $msg], 422);
    //             }

    //             $productId   = $variation->product_id;
    //             $variationId = $variation->id;
    //             $warehouseId = $stockRecord->warehouse_id;
    //             $binId       = $stockRecord->bin_id;


    //             Cart::add([
    //                 'id'      => 'var_' . $variation->id,
    //                 'name'    => $variation->product->title,
    //                 'qty'     => $qty,
    //                 'price'   => $variation->final_price,
    //                 'weight'  => 0,
    //                 'options' => [
    //                     'slug'          => $variation->product->slug,
    //                     'variation_id'  => $variation->id,
    //                     'thumbnail'     => $variation->product->thumbnail_url,
    //                     'variant'       => $variation->display_name,
    //                     'regular_price' => $variation->regular_price,
    //                     'warehouse_id'  => $warehouseId,
    //                     'bin_id'        => $binId ?? null,
    //                 ],
    //             ]);
    //             Log::info("Cart Add (Variation); Product ID: {$productId}, Variation ID: {$variationId}, Warehouse ID: {$warehouseId}, Bin ID: {$binId}, Qty: {$qty}");
    //         } else {

    //             // ── SINGLE PRODUCT ──
    //             $product = Product::findOrFail($request->id);

    //             // warehouse_info
    //             $bestWarehouse = null;
    //             $bestBinId     = null;
    //             $bestQty       = 0;

    //             if (!empty($product->warehouse_info)) {
    //                 foreach ($product->warehouse_info as $info) {
    //                     $q   = (int) ($info['quantity'] ?? 0);
    //                     $wId = (int) ($info['warehouse_id'] ?? 0);
    //                     $bId = $info['bin_id'] ?? null;

    //                     if ($wId > 0 && $q > $bestQty) {
    //                         $bestQty       = $q;
    //                         $bestWarehouse = $wId;
    //                         $bestBinId     = $bId;
    //                     }
    //                 }
    //             }

    //             $alreadyInCart    = $this->getCartQty($product->id, null);
    //             $availableForCart = $bestQty - $alreadyInCart;

    //             if ($bestQty < $qty || $availableForCart < $qty) {
    //                 $msg = $alreadyInCart > 0
    //                     ? "Only {$availableForCart} more can be added. (Already {$alreadyInCart} in cart)"
    //                     : 'Sorry, this product is currently out of stock.';
    //                 return response()->json(['status' => 'error', 'message' => $msg], 422);
    //             }

    //             $productId   = $product->id;
    //             $variationId = null;
    //             $warehouseId = $bestWarehouse;
    //             $binId       = $bestBinId;

    //             Cart::add([
    //                 'id'      => $product->id,
    //                 'name'    => $product->title,
    //                 'qty'     => $qty,
    //                 'price'   => $product->sale_price,
    //                 'weight'  => 0,
    //                 'options' => [
    //                     'slug'          => $product->slug,
    //                     'thumbnail'     => $product->thumbnail_url,
    //                     'regular_price' => $product->regular_price,
    //                     'warehouse_id'  => $warehouseId,
    //                     'bin_id'        => $binId ?? null,
    //                 ],
    //             ]);
    //             Log::info("Cart Add (Single Product); Product ID: {$productId}, Warehouse ID: {$warehouseId}, Bin ID: {$binId}, Qty: {$qty}");
    //         }
    //         $customerId = auth('customer')->check() ? auth('customer')->id() : null;

    //         $finalCompanyId = $this->company_id;

    //         if (!$finalCompanyId) {
    //             if (auth('customer')->check()) {
    //                 $finalCompanyId = auth('customer')->user()->company_id;
    //             } else {
    //                 $productForId = Product::find($productId);
    //                 $finalCompanyId = $productForId ? $productForId->company_id : null;
    //             }
    //         }


    //         if ($finalCompanyId) {
    //             CartTrack::updateOrCreate(
    //                 [
    //                     'session_id'   => session()->getId(),
    //                     'product_id'   => $productId,
    //                     'variation_id' => $variationId,
    //                 ],
    //                 [
    //                     'company_id'  => $finalCompanyId,
    //                     'customer_id' => $customerId,
    //                     'quantity'    => $qty,
    //                     'status'      => CartTrack::ADDED,
    //                 ]
    //             );
    //         } else {
    //             Log::error("Cart Tracking Error: Could not determine company_id for product {$productId}");
    //         }

    //         return response()->json([
    //             'status'     => 'success',
    //             'cart_count' => Cart::count(),
    //             'message'    => 'Successfully added to cart!',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Sorry, there was an issue: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Return how much quantity of this product/variation is already in the cart.
     */
    private function getCartQty(?int $productId, ?int $variationId): int
    {
        $total = 0;
        foreach (Cart::content() as $item) {
            if ($variationId !== null) {
                // Variation match
                if (($item->options->variation_id ?? null) == $variationId) {
                    $total += $item->qty;
                }
            } else {
                // Single product match
                if (is_numeric($item->id) && (int) $item->id === $productId) {
                    $total += $item->qty;
                }
            }
        }
        return $total;
    }
    //update cart
    public function update(Request $request)
    {
        $item = Cart::get($request->rowId);
        if ($item) {
            $productId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
            CartTrack::where('session_id', session()->getId())
                ->where('product_id', $productId)
                ->update(['quantity' => $request->qty, 'status' => CartTrack::ADDED]);
        }

        Cart::update($request->rowId, $request->qty);

        // AJAX request হলে JSON response
        if ($request->ajax() || $request->wantsJson()) {
            $subtotalFloat = (float) str_replace(',', '', Cart::subtotal());

            $discount = 0;
            if (session()->has('coupon')) {
                // Re-validate coupon against new subtotal
                $couponResult = (new \App\Services\CouponService())->validateCoupon(
                    session('coupon')['coupon_code'],
                    $subtotalFloat,
                    auth('customer')->id() ?? null
                );
                if ($couponResult['success']) {
                    session()->put('coupon', $couponResult);
                    $discount = $couponResult['discount_amount'];
                } else {
                    session()->forget('coupon');
                }
            }

            $settings = \App\Models\SiteSetting::where('company_id', $this->company_id)->first();
            $shipping = session()->get('shipping_cost', $settings->inside_charge ?? 60);
            $total = ($subtotalFloat - $discount) + $shipping;

            return response()->json([
                'status' => 'success',
                'message' => 'Cart has been updated.',
                'cart_count' => Cart::count(),
                'subtotal' => Cart::subtotal(),
                'discount' => number_format($discount),
                'shipping' => number_format($shipping),
                'total' => number_format($total)
            ]);
        }

        // সাধারণ form submit হলে আগের মতোই redirect
        return back()->with('success', 'Cart has been updated.');
    }

    public function remove($rowId)
    {
        $item = Cart::get($rowId);
        if ($item) {
            $productId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);

            CartTrack::where('session_id', session()->getId())
                ->where('product_id', $productId)
                ->update(['status' => CartTrack::REMOVED]);
        }
        Cart::remove($rowId);
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => 'success', 'cart_count' => Cart::count()]);
        }
        return back()->with('success', 'The item has been successfully removed from your cart!');
    }
    public function getCartDrawerItems()
    {
        return view('components.template1.cart-drawer-items')->render();
    }
}

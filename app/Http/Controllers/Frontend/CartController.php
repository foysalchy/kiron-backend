<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart as CartTrack;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\CouponService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Models\Cart as CartModel;

class CartController extends FrontendController
{
    public function __construct(protected CouponService $couponService) {}
    public function index($store)
    {


        $cartContent = Cart::content();
        $subtotal = (float) str_replace(',', '', Cart::subtotal());

        //delivvery charge default=60
        $shipping = session()->get('shipping_cost', 60);
        $shipping_area = session()->get('shipping_area', 'inside');

        //coupon discount
        $discount = 0;

        if (session()->has('coupon')) {
            try {
                $couponSession = session()->get('coupon');
                $customerId = auth('customer')->id();

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

        return $this->view(
            'frontend.cart',
            compact(
                'cartContent',
                'subtotal',
                'discount',
                'shipping',
                'total',
                'shipping_area'
            )
        );
    }
    //shipping area method
    public function updateShipping($store, Request $request)
    {
        $cost = ($request->area == 'outside') ? 120 : 60;

        session()->put('shipping_area', $request->area);
        session()->put('shipping_cost', $cost);

        if ($request->ajax() || $request->wantsJson()) {
            $subtotal = (float) str_replace(',', '', Cart::subtotal());

            //
            $discount = 0;
            if (session()->has('coupon')) {
                try {
                    $couponSession = session()->get('coupon');
                    $result = $this->couponService->validateCoupon(
                        $couponSession['coupon_code'],
                        $subtotal,
                        auth('customer')->id()
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

    public function applyCoupon($store, Request $request)
    {
        try {
            $subtotal = (float) str_replace(',', '', Cart::subtotal());
            $customerId = auth('customer')->id();

            $result = $this->couponService->validateCoupon(
                $request->coupon_code,
                $subtotal,
                $customerId
            );

            session()->put('coupon', $result);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
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

    public function removeCoupon($store)
    {
        session()->forget('coupon');
        return back()->with('success', 'The coupon has been removed.');
    }
    // product add to cart
    public function add($store, Request $request)
    {
        try {
            $qty       = (int) ($request->qty ?? 1);
            $productId   = null;
            $variationId = null;

            if ($request->filled('variation_id')) {

                $variation = ProductVariation::with('product')->findOrFail($request->variation_id);

                // Check available stock excluding quantity already in the cart
                $alreadyInCart = $this->getCartQty(null, $variation->id);
                $availableForCart = $variation->available_stock - $alreadyInCart;

                if ($availableForCart < $qty) {
                    $msg = $alreadyInCart > 0
                        ? "Only {$availableForCart} more can be added. (Already {$alreadyInCart} in cart)"
                        : 'Sorry, this variation is currently out of stock.';

                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }

                $productId   = $variation->product_id;
                $variationId = $variation->id;
                $thumb       = $variation->image
                    ? asset('storage/' . $variation->image)
                    : $variation->product->thumbnail_url;

                Cart::add([
                    'id'      => 'var_' . $variation->id,
                    'name'    => $variation->product->title,
                    'qty'     => $qty,
                    'price'   => $variation->final_price,
                    'weight'  => 0,
                    'options' => [
                        'variation_id'  => $variation->id,
                        'thumbnail'     => $thumb,
                        'variant'       => $variation->display_name,
                        'regular_price' => $variation->regular_price,
                    ],
                ]);
            } else {

                $product = Product::findOrFail($request->id);

                // Check available stock excluding quantity already in the cart
                $alreadyInCart    = $this->getCartQty($product->id, null);
                $availableForCart = $product->available_stock - $alreadyInCart;

                if ($availableForCart < $qty) {
                    $msg = $alreadyInCart > 0
                        ? "Only {$availableForCart} more can be added. (Already {$alreadyInCart} in cart)"
                        : 'Sorry, this product is currently out of stock.';

                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }

                $productId   = $product->id;
                $variationId = null;

                Cart::add([
                    'id'      => $product->id,
                    'name'    => $product->title,
                    'qty'     => $qty,
                    'price'   => $product->sale_price,
                    'weight'  => 0,
                    'options' => [
                        'thumbnail'     => $product->thumbnail_url,
                        'regular_price' => $product->regular_price,
                    ],
                ]);
            }

            // Database tracking
            CartTrack::updateOrCreate(
                [
                    'session_id'   => session()->getId(),
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                ],
                [
                    'company_id'  => getCurrentCompany()->id,
                    'customer_id' => auth('customer')->id(),
                    'quantity'    => $qty,
                    'status'      => CartTrack::ADDED,
                ]
            );

            return response()->json([
                'status'     => 'success',
                'cart_count' => Cart::count(),
                'message'    => 'Successfully added to cart!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, there was an issue: ' . $e->getMessage(),
            ], 500);
        }
    }

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
    public function update($store, Request $request)
    {
        $item = Cart::get($request->rowId);
        if ($item) {
            $productId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);
            CartTrack::where('session_id', session()->getId())
                ->where('product_id', $productId)
                ->update(['quantity' => $request->qty, 'status' => CartTrack::ADDED]);
        }
        Cart::update($request->rowId, $request->qty);
        return back()->with('success', 'Cart has been updated.');
    }

    public function remove($store, $rowId)
    {
        $item = Cart::get($rowId);
        if ($item) {
            $productId = is_numeric($item->id) ? $item->id : str_replace('var_', '', $item->id);

            CartTrack::where('session_id', session()->getId())
                ->where('product_id', $productId)
                ->update(['status' => CartTrack::REMOVED]);
        }
        Cart::remove($rowId);
        return back()->with('success', 'The item has been successfully removed from your cart!');
    }
}

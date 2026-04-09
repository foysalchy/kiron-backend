<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\CouponService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CouponService $couponService)
    {

    }
    public function index($store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

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

        return view($template . '.frontend.cart',
        compact(
            'cartContent',
            'subtotal',
            'discount',
            'shipping',
            'total',
            'shipping_area'
        ));
    }
    //shipping area method
    public function updateShipping($store, Request $request)
    {
        $cost = ($request->area == 'outside') ? 120 : 60;

        session()->put('shipping_area', $request->area);
        session()->put('shipping_cost', $cost);

        return back()->with('success', 'ডেলিভারি এরিয়া আপডেট করা হয়েছে।');
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

            return back()->with('success', 'অভিনন্দন! কুপনটি সফলভাবে যুক্ত হয়েছে।');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeCoupon($store)
    {
        session()->forget('coupon');
        return back()->with('success', 'কুপনটি সরানো হয়েছে।');
    }
    // product add to cart
    public function add($store,Request $request)
    {
        try {
            $qty = (int) ($request->qty ?? 1);

            if ($request->filled('variation_id')) {
                $variation = ProductVariation::with('product')->findOrFail($request->variation_id);
                $thumb = $variation->image
                    ? asset('storage/' . $variation->image)
                    : $variation->product->thumbnail_url;
                Cart::add([
                    'id'      => 'var_' . $variation->id,
                    'name'    => $variation->product->title,
                    'qty'     => $qty,
                    'price'   => $variation->final_price,
                    'weight'  => 0,
                    'options' => [
                        'variation_id' => $variation->id,
                        'thumbnail'    => $thumb,
                        'variant'      => $variation->display_name,
                        'regular_price' => $variation->regular_price
                    ]
                ]);

            } else {
                $product = Product::findOrFail($request->id);

                Cart::add([
                    'id'      => $product->id,
                    'name'    => $product->title,
                    'qty'     => $qty,
                    'price'   => $product->sale_price,
                    'weight'  => 0,
                    'options' => [
                        'thumbnail' => $product->thumbnail_url,
                        'regular_price' => $product->regular_price
                    ]
                ]);
            }

            return response()->json([
                'status'     => 'success',
                'cart_count' => Cart::count(),
                'message'    => 'সফলভাবে কার্টে যোগ করা হয়েছে!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'দুঃখিত, সমস্যা হয়েছে: ' . $e->getMessage()
            ], 500);
        }
    }
    //update cart
    public function update($store, Request $request)
    {
        Cart::update($request->rowId, $request->qty);
        return back()->with('success', 'কার্ট আপডেট হয়েছে');
    }

    public function remove($store, $rowId)
    {
        Cart::remove($rowId);
        return back()->with('success', 'পণ্যটি আপনার কার্ট থেকে সফলভাবে সরানো হয়েছে!');
    }

}

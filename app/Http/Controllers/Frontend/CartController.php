<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add($store, Request $request)
    {
        try {
            $qty = $request->qty ?? 1;

            if ($request->filled('variation_id')) {
                $variation = ProductVariation::with('product')->findOrFail($request->variation_id);
                Cart::add([
                    'id'      => 'var_' . $variation->id, // এখানে ID টি ইউনিক করে দিন
                    'name'    => $variation->product->title,
                    'qty'     => $qty,
                    'price'   => $variation->final_price,
                    'weight'  => 0,
                    'options' => [
                        'variation_id' => $variation->id,
                        'thumbnail'    => $variation->image ?? $variation->product->thumbnail_url,
                        'variant'      => $variation->display_name
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
                        'thumbnail' => $product->thumbnail_url
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
}

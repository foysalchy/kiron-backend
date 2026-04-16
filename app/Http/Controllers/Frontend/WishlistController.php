<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle($store, Request $request)
    {
        if (!auth('customer')->check()) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Please log in before adding items to your wishlist.'
            ]);
        }

        $company = getCurrentCompany();
        $productId = $request->product_id;
        $customerId = auth('customer')->id();

        $product = Product::where('id', $productId)
                ->where('company_id', $company->id)
                ->first();

    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found.'
        ]);
    }

        $exists = Wishlist::where('company_id', $company->id)
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($exists) {
            $exists->delete();
            $res_status = 'removed';
            $message = 'The item has been removed from the wishlist.';
        } else {
            Wishlist::create([
                'company_id'  => $company->id,
                'customer_id' => $customerId,
                'product_id'  => $productId,
            ]);
            $res_status = 'added';
            $message = 'The item has been added to the wishlist.';
        }

        $wishCount = Wishlist::where('customer_id', $customerId)->count();

        return response()->json([
            'status' => $res_status,
            'message' => $message,
            'wish_count' => $wishCount
        ]);
    }
}

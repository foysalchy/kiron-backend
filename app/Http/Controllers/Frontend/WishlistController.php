<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle($store, Request $request)
    {
        if (!auth('customer')->check()) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'উইশলিস্টে যোগ করতে আগে লগইন করুন।'
            ]);
        }

        $company = getCurrentCompany();
        $productId = $request->product_id;
        $customerId = auth('customer')->id();

        $exists = Wishlist::where('company_id', $company->id)
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($exists) {
            $exists->delete();
            $res_status = 'removed';
            $message = 'পণ্যটি উইশলিস্ট থেকে সরানো হয়েছে।';
        } else {
            Wishlist::create([
                'company_id'  => $company->id,
                'customer_id' => $customerId,
                'product_id'  => $productId,
            ]);
            $res_status = 'added';
            $message = 'পণ্যটি উইশলিস্টে যোগ করা হয়েছে।';
        }

        $wishCount = Wishlist::where('customer_id', $customerId)->count();

        return response()->json([
            'status' => $res_status,
            'message' => $message,
            'wish_count' => $wishCount
        ]);
    }
}

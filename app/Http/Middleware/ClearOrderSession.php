<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClearOrderSession
{
    public function handle(Request $request, Closure $next)
    {
        // যে পেজগুলোতে সেশনটি রাখা যাবে (Route Names)
        $allowedRoutes = [
            'cart.index',      // কার্ট পেজের রাউট নাম
            'checkout.index',  // চেকআউট পেজের রাউট নাম
            'order.store',     // অর্ডার সাবমিট করার রাউট
            'payment.submit',   // পেমেন্ট সাবমিট রাউট
            // আপনার ইনভয়েস বা সাকসেস পেজ থাকলে সেটিও এখানে দিতে পারেন
        ];

        // বর্তমান রাউটটি যদি অ্যালাউড লিস্টে না থাকে
        if (!in_array($request->route()->getName(), $allowedRoutes)) {
            // ড্রাফট অর্ডারের সেশনটি রিমুভ করে দিন
            Session::forget('current_draft_order_id');
            // আপনি চাইলে শিপিং কস্ট বা কুপন সেশনও রিমুভ করতে পারেন
            // Session::forget(['shipping_cost', 'coupon']);
        }

        return $next($request);
    }
}

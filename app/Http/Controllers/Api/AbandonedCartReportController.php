<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbandonedCartReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Status 1 = Added (not purchased), updated more than 2 hours ago
        $query = Cart::with(['product' => function($q) {
                $q->select('id', 'title', 'sale_price');
            }])
            ->where('status', Cart::ADDED)
            ->where('updated_at', '<=', Carbon::now()->subHours(2));

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $carts = $query->get();

        $report = [];
        foreach ($carts as $cart) {
            $identifier = $cart->customer_id ?? $cart->session_id ?? 'Unknown';

            if (!isset($report[$identifier])) {
                $report[$identifier] = [
                    'identifier' => $identifier,
                    'is_guest' => is_null($cart->customer_id),
                    'items_count' => 0,
                    'potential_revenue' => 0,
                    'last_active' => $cart->updated_at->diffForHumans(),
                ];
            }

            $report[$identifier]['items_count'] += $cart->quantity;
            
            if ($cart->product) {
                $report[$identifier]['potential_revenue'] += ($cart->product->sale_price * $cart->quantity);
            }
        }

        return ResponseHelper::success(array_values($report));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingRoiReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = Order::query()
            ->whereNotNull('source_info');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $report = [];
        foreach ($orders as $order) {
            $sourceInfo = is_array($order->source_info) ? $order->source_info : json_decode($order->source_info, true);
            $utmSource = $sourceInfo['utm_source'] ?? $sourceInfo['source'] ?? 'Direct/Organic';

            if (!isset($report[$utmSource])) {
                $report[$utmSource] = [
                    'source' => $utmSource,
                    'total_orders' => 0,
                    'total_revenue' => 0,
                ];
            }

            $report[$utmSource]['total_orders']++;
            $report[$utmSource]['total_revenue'] += $order->grand_total ?? $order->total_amount ?? 0;
        }

        return ResponseHelper::success(array_values($report));
    }
}

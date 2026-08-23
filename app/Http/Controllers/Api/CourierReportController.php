<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourierReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = Order::query()
            ->whereNotNull('courier_info');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $report = [];
        foreach ($orders as $order) {
            $courierName = $order->courier_info['courier_name'] ?? $order->courier_info['name'] ?? 'Unknown';
            $status = strtolower($order->status); // Now uses self_status from webhook if applied

            if (!isset($report[$courierName])) {
                $report[$courierName] = [
                    'courier' => $courierName,
                    'total_orders' => 0,
                    'delivered' => 0,
                    'cancelled' => 0,
                    'fake' => 0,
                    'hold' => 0,
                    'pending' => 0,
                    'rts_cost' => 0,
                ];
            }

            $report[$courierName]['total_orders']++;

            if (str_contains($status, 'delivered')) {
                $report[$courierName]['delivered']++;
            } elseif (str_contains($status, 'cancelled') || str_contains($status, 'rts') || str_contains($status, 'return')) {
                $report[$courierName]['cancelled']++;
                $report[$courierName]['rts_cost'] += $order->other_charges ?? 0;
            } elseif (str_contains($status, 'fake')) {
                $report[$courierName]['fake']++;
                $report[$courierName]['rts_cost'] += $order->other_charges ?? 0;
            } elseif (str_contains($status, 'hold')) {
                $report[$courierName]['hold']++;
            } else {
                $report[$courierName]['pending']++;
            }
        }

        // Calculate success rate
        $finalReport = array_values($report);
        foreach ($finalReport as &$row) {
            $row['success_rate'] = $row['total_orders'] > 0 
                ? round(($row['delivered'] / $row['total_orders']) * 100, 2) . '%' 
                : '0%';
        }

        return ResponseHelper::success($finalReport);
    }
}

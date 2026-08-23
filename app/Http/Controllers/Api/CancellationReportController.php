<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancellationReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = Order::query()
            ->whereIn('status', ['cancelled', 'fake', 'returned', 'rts']); // self_status mapped ones

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $report = [];
        foreach ($orders as $order) {
            $status = strtolower($order->status);

            if (!isset($report[$status])) {
                $report[$status] = [
                    'status' => ucfirst($status),
                    'count' => 0,
                    'lost_revenue' => 0,
                    'lost_courier_charge' => 0,
                ];
            }

            $report[$status]['count']++;
            $report[$status]['lost_revenue'] += $order->grand_total ?? $order->total_amount ?? 0;
            $report[$status]['lost_courier_charge'] += $order->other_charges ?? 0;
        }

        return ResponseHelper::success(array_values($report));
    }
}

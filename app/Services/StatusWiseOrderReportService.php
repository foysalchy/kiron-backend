<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class StatusWiseOrderReportService
{
    public function generate(string $startDate, string $endDate): array
    {
        // Relevant Order Statuses with labels
        $orderStatuses = [
            Status::Pending->value           => 'Pending',
            Status::Confirmed->value         => 'Confirmed',
            Status::Processing->value        => 'Processing',
            Status::ReadyToShipped->value    => 'Ready to Ship',
            Status::Shipped->value           => 'Shipped',
            Status::HandovertoCourier->value => 'Handover to Courier',
            Status::InTransit->value         => 'In Transit',
            Status::Delivered->value         => 'Delivered',
            Status::ReturntoCourier->value   => 'Return to Courier',
            Status::ReturnReceived->value    => 'Return Received',
            Status::ReturnRequest->value     => 'Return Request',
            Status::Cancelled->value         => 'Cancelled',
            Status::Fake->value              => 'Fake',
        ];

        $statusKeys = array_keys($orderStatuses);

        // Fetch aggregated data per status
        $rawStats = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $statusKeys)
            ->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id')
            ->select(
                'orders.status',
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_qty'),
                DB::raw('COALESCE(SUM(DISTINCT orders.grand_total), 0) as total_amount')
            )
            ->groupBy('orders.status')
            ->get()
            ->keyBy('status');

        // Total orders calculation for overall summary
        $totalOrdersCount = (int) Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $statusKeys)
            ->count();

        $totalAmountSum = (float) Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $statusKeys)
            ->sum('grand_total');

        $totalQtySum = (int) DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->whereIn('orders.status', $statusKeys)
            ->sum('order_details.quantity');

        $statusBreakdown = [];
        foreach ($orderStatuses as $statusId => $statusName) {
            $stat = $rawStats->get($statusId);
            $count = (int) ($stat->order_count ?? 0);
            $qty = (int) ($stat->total_qty ?? 0);

            // Fetch accurate sum of grand_total for this status
            $amount = (float) Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('status', $statusId)
                ->sum('grand_total');

            $percentage = $totalOrdersCount > 0 ? round(($count / $totalOrdersCount) * 100, 2) : 0;

            $statusBreakdown[] = [
                'status_id'   => $statusId,
                'status_name' => $statusName,
                'order_count' => $count,
                'total_qty'   => $qty,
                'amount'      => round($amount, 2),
                'percentage'  => $percentage,
            ];
        }

        return [
            'date_range' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
            'summary' => [
                'total_orders' => $totalOrdersCount,
                'total_qty'    => $totalQtySum,
                'total_amount' => round($totalAmountSum, 2),
            ],
            'statuses' => $statusBreakdown,
        ];
    }
}


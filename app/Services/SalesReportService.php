<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;


class SalesReportService
{
    public function generate(string $startDate, string $endDate): array
    {
        $allStatuses = [
            Status::Pending->value,
            Status::Confirmed->value,
            Status::Processing->value,
            Status::ReadyToShipped->value,
            Status::Shipped->value,
            Status::HandovertoCourier->value,
            Status::InTransit->value,
            Status::Delivered->value,
            Status::ReturntoCourier->value,
            Status::ReturnReceived->value,
            Status::ReturnRequest->value,
            Status::Cancelled->value,
        ];

        $deliveredStatuses  = [Status::Delivered->value];
        $cancelledStatuses  = [Status::Cancelled->value];
        $returnedStatuses   = [Status::ReturntoCourier->value, Status::ReturnReceived->value, Status::ReturnRequest->value];

        // Order Summary
        $total     = $this->orderSummary($startDate, $endDate, $allStatuses);
        $delivered = $this->orderSummary($startDate, $endDate, $deliveredStatuses);
        $cancelled = $this->orderSummary($startDate, $endDate, $cancelledStatuses);
        $returned  = $this->orderSummary($startDate, $endDate, $returnedStatuses);
        $returnReceived = $this->orderSummary($startDate, $endDate, [Status::ReturnReceived->value]);

        // Sales Summary (delivered only)
        $deliveredOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $deliveredStatuses);

        $totalSales    = $deliveredOrders->sum('grand_total');
        $totalDiscount = $deliveredOrders->selectRaw('SUM(discount_on_all + coupon_discount) as total')->value('total') ?? 0;
        $totalShipping = $deliveredOrders->sum('other_charges');
        $netSales      = $totalSales;

        // Payment
        $totalCollected = $deliveredOrders->sum('payment_amount');
        $totalDue       = $netSales - $totalCollected;

        // Rates
        $totalCount = $total['count'] ?: 1;

        return [
            'date_range'    => ['start' => $startDate, 'end' => $endDate],
            'order_summary' => [
                'total_orders'     => $total,
                'delivered_orders' => $delivered,
                'cancelled_orders' => $cancelled,
                'return_orders'    => $returned,
                'return_received'    => $returnReceived,
            ],
            'sales_summary' => [
                'total_sales'       => round($totalSales, 2),
                'total_discount'    => round($totalDiscount, 2),
                'total_shipping'    => round($totalShipping, 2),
                'net_sales'         => round($netSales, 2),
                'total_collected'   => round($totalCollected, 2),
                'total_due'         => round($totalDue, 2),
                'delivery_rate'     => round(($delivered['count'] / $totalCount) * 100, 2),
                'cancellation_rate' => round(($cancelled['count'] / $totalCount) * 100, 2),
                'return_rate'       => round(($returned['count']  / $totalCount) * 100, 2),
                'collection_rate'   => $netSales > 0 ? round(($totalCollected / $netSales) * 100, 2) : 0,
            ],
        ];
    }

    private function orderSummary(string $start, string $end, array $statuses): array
    {
        $result = Order::whereBetween('order_date', [$start, $end])
            ->whereIn('status', $statuses)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as amount')
            ->first();

        return [
            'count'  => (int) ($result->count  ?? 0),
            'amount' => (float) ($result->amount ?? 0),
        ];
    }
}

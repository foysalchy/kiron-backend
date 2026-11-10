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
            Status::Fake->value,
        ];

        // Specific Status Groups
        $processingStatuses = [
            Status::Pending->value,
            Status::Confirmed->value,
            Status::Processing->value,
            Status::ReadyToShipped->value,
        ];
        $shippingStatuses   = [
            Status::Shipped->value,
            Status::HandovertoCourier->value,
            Status::InTransit->value,
        ];
        $deliveredStatuses  = [Status::Delivered->value];
        $returnedStatuses   = [
            Status::ReturntoCourier->value,
            Status::ReturnReceived->value,
            Status::ReturnRequest->value,
        ];
        $cancelledStatuses  = [Status::Cancelled->value];
        $fakeStatuses       = [Status::Fake->value];

        // Order Summary Groups
        $total            = $this->orderSummary($startDate, $endDate, $allStatuses);
        $processingGroup  = $this->orderSummary($startDate, $endDate, $processingStatuses);
        $shippingGroup    = $this->orderSummary($startDate, $endDate, $shippingStatuses);
        $delivered        = $this->orderSummary($startDate, $endDate, $deliveredStatuses);
        $returned         = $this->orderSummary($startDate, $endDate, $returnedStatuses);
        $returnReceived   = $this->orderSummary($startDate, $endDate, [Status::ReturnReceived->value]);
        $cancelled        = $this->orderSummary($startDate, $endDate, $cancelledStatuses);
        $fake             = $this->orderSummary($startDate, $endDate, $fakeStatuses);

        // Sales Summary (delivered only)
        $deliveredOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $deliveredStatuses);

        $totalSales    = (float) $deliveredOrders->sum('grand_total');
        $totalDiscount = (float) ($deliveredOrders->selectRaw('SUM(discount_on_all + coupon_discount) as total')->value('total') ?? 0);
        $totalShipping = (float) $deliveredOrders->sum('other_charges');
        $netSales      = $totalSales;

        // Payment
        $totalCollected = (float) $deliveredOrders->sum('payment_amount');
        $totalDue       = $netSales - $totalCollected;

        // Rates
        $totalCount = $total['count'] ?: 1;

        return [
            'date_range'    => ['start' => $startDate, 'end' => $endDate],
            'order_summary' => [
                'total_orders'     => $total,
                'processing_group' => $processingGroup,
                'shipping_group'   => $shippingGroup,
                'delivered_orders' => $delivered,
                'return_orders'    => $returned,
                'return_received'  => $returnReceived,
                'cancelled_orders' => $cancelled,
                'fake_orders'      => $fake,
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

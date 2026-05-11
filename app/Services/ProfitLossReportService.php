<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\PurchaseDetail;
use App\Models\TransactionExpense;
use Illuminate\Support\Facades\DB;

class ProfitLossReportService
{
    // Shipped statuses — courier এ গেছে
    private array $shippedStatuses = [
        Status::Shipped->value,
        Status::HandovertoCourier->value,
        Status::InTransit->value,
        Status::Delivered->value,
        Status::ReturntoCourier->value,
        Status::ReturnReceived->value,
    ];

    // Delivered statuses — successfully delivered
    private array $deliveredStatuses = [
        Status::Delivered->value,
    ];

    // Returned statuses
    private array $returnedStatuses = [
        Status::ReturntoCourier->value,
        Status::ReturnReceived->value,
        Status::ReturnRequest->value,
    ];

    public function generate(string $startDate, string $endDate): array
    {
        // ── 1. Order Summary ──────────────────────────────
        $shippedOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->shippedStatuses)
            ->selectRaw('COUNT(*) as count, SUM(grand_total) as total')
            ->first();

        $deliveredOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->selectRaw('COUNT(*) as count, SUM(grand_total) as total')
            ->first();

        $returnedOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->returnedStatuses)
            ->selectRaw('COUNT(*) as count, SUM(grand_total) as total')
            ->first();

        // ── 2. Product Purchase Cost (Delivered orders only) ──
        $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('id');

        $orderDetails = OrderDetail::whereIn('order_id', $deliveredOrderIds)
            ->with(['product', 'variation'])
            ->get();

        $totalPurchaseCost = 0;

        foreach ($orderDetails as $detail) {
            $purchasePrice     = $this->resolvePurchasePrice($detail);
            $totalPurchaseCost += $purchasePrice * $detail->quantity;
        }

        // ✅ qty = delivered order count (not product qty sum)
        $purchaseQty = $deliveredOrders->count ?? 0;

        // ── 3. Shipping Cost (Shipped + Returned) ──
        $shippingCost = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->shippedStatuses)
            ->sum('other_charges');

        $shippingQty = $shippedOrders->count ?? 0;

        $approvedExpense = TransactionExpense::whereBetween('date', [$startDate, $endDate])
            ->where('status', Status::Approved->value)
            ->sum('total_amount');

        $totalCost = $totalPurchaseCost + $shippingCost + (float) $approvedExpense;
        // ── 5. Totals ──
        $deliveredSales = $deliveredOrders->total ?? 0;
        $profitLoss     = $deliveredSales - $totalCost;

        $returnRate = ($shippedOrders->count ?? 0) > 0
            ? round(($returnedOrders->count / $shippedOrders->count) * 100, 2)
            : 0;

        $profitMargin = $deliveredSales > 0
            ? round(($profitLoss / $deliveredSales) * 100, 2)
            : 0;
        $returnReceivedOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', Status::ReturnReceived->value)
            ->selectRaw('COUNT(*) as count, SUM(grand_total) as total')
            ->first();
        return [
            'date_range' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
            'order_summary' => [
                'total_shipped'   => ['count' => $shippedOrders->count   ?? 0, 'amount' => $shippedOrders->total   ?? 0],
                'total_delivered' => ['count' => $deliveredOrders->count ?? 0, 'amount' => $deliveredOrders->total ?? 0],
                'total_returned'  => ['count' => $returnedOrders->count  ?? 0, 'amount' => $returnedOrders->total  ?? 0],
                'return_received' => [
                    'count'  => $returnReceivedOrders->count ?? 0,
                    'amount' => $returnReceivedOrders->total ?? 0,
                ],
            ],
            'cost_breakdown' => [
                'product_purchase_cost' => [
                    'qty'    => $purchaseQty,  // delivered order count
                    'amount' => round($totalPurchaseCost, 2)
                ],
                'shipping_cost'         => ['qty' => $shippingQty,  'amount' => round($shippingCost, 2)],
                'ads_expense'           => ['qty' => null,           'amount' => round($approvedExpense, 2)],
                'total_cost'            => round($totalCost, 2),
            ],
            'profit_loss' => [
                'delivered_sales' => round($deliveredSales, 2),
                'total_expense'   => round($totalCost, 2),
                'net_profit'      => round($profitLoss, 2),
                'return_rate'     => $returnRate,
                'profit_margin'   => $profitMargin,
                'is_profit'       => $profitLoss >= 0,
            ],
        ];
    }

    private function resolvePurchasePrice(OrderDetail $detail): float
    {
        if ($detail->variation_id) {
            // 1. Purchase table
            $lastPurchase = PurchaseDetail::where('variation_id', $detail->variation_id)
                ->orderByDesc('created_at')
                ->value('purchase_price');

            if ($lastPurchase) return (float) $lastPurchase;

            // 2. Variation table 
            $variationPrice = $detail->variation?->purchase_price;

            if ($variationPrice) return (float) $variationPrice;

            // 3. ✅ Parent product table থেকে (fallback)
            return (float) ($detail->product?->purchase_price ?? 0);
        }

        // Single product
        // 1. Purchase table থেকে
        $lastPurchase = PurchaseDetail::where('product_id', $detail->product_id)
            ->whereNull('variation_id')
            ->orderByDesc('created_at')
            ->value('purchase_price');

        if ($lastPurchase) return (float) $lastPurchase;

        // 2. Product table থেকে
        return (float) ($detail->product?->purchase_price ?? 0);
    }
}

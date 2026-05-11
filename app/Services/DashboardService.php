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
use Carbon\Carbon;

class DashboardService
{
    private array $deliveredStatuses = [Status::Delivered->value];

    public function overview(string $period = 'last_30_days'): array
    {
        [$startDate, $endDate, $prevStartDate, $prevEndDate] = $this->resolveDateRange($period);

        // ── Current Period Data ──
        $currentData = $this->calculatePeriodData($startDate, $endDate);

        // ── Previous Period Data (for growth comparison) ──
        $previousData = $this->calculatePeriodData($prevStartDate, $prevEndDate);

        // ── Growth Calculation ──
        $growth = $this->calculateGrowth($currentData, $previousData);

        // ── Top Selling Products ──
        $topProducts = $this->getTopSellingProducts($startDate, $endDate, 5);

        // ── Sales Trend (Daily breakdown for chart) ──
        $salesTrend = $this->getSalesTrend($startDate, $endDate);

        // ── Sales by Category ──
        $salesByCategory = $this->getSalesByCategory($startDate, $endDate);

        // ── Recent Orders ──


        return [
            'period' => [
                'label'       => $this->getPeriodLabel($period),
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'compare_with' => "{$prevStartDate} to {$prevEndDate}",
            ],
            'summary' => [
                'total_revenue'  => round($currentData['revenue'], 2),
                'total_expense'  => round($currentData['expense'], 2),
                'net_profit'     => round($currentData['profit'], 2),
                'total_orders'   => $currentData['orders_count'],
            ],
            'growth' => $growth,
            'top_products' => $topProducts,
            'sales_trend' => $salesTrend,
            'sales_by_category' => $salesByCategory,

        ];
    }

    // ── Calculate Period Data ──────────────────────────
    private function calculatePeriodData(string $startDate, string $endDate): array
    {
        $deliveredOrders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses);

        $revenue = $deliveredOrders->sum('grand_total');
        $ordersCount = $deliveredOrders->count();

        // Expense calculation
        $deliveredOrderIds = $deliveredOrders->pluck('id');

        $orderDetails = OrderDetail::whereIn('order_id', $deliveredOrderIds)
            ->with(['product', 'variation'])
            ->get();

        $purchaseCost = 0;
        foreach ($orderDetails as $detail) {
            $purchasePrice = $this->resolvePurchasePrice($detail->product_id, $detail->variation_id);
            $purchaseCost += $purchasePrice * $detail->quantity;
        }

        $shippingCost = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->sum('other_charges');

        // Static ads expense (will be dynamic later)
        $approvedExpense = TransactionExpense::whereBetween('date', [$startDate, $endDate])
            ->where('status', Status::Approved->value)
            ->sum('total_amount');

        $totalExpense = $purchaseCost + $shippingCost + (float) $approvedExpense;
        $profit = $revenue - $totalExpense;

        return [
            'revenue'      => $revenue,
            'expense'      => $totalExpense,
            'profit'       => $profit,
            'orders_count' => $ordersCount,
        ];
    }

    // ── Growth Calculation ──────────────────────────────
    private function calculateGrowth(array $current, array $previous): array
    {
        $revenueGrowth = $this->percentageGrowth($current['revenue'], $previous['revenue']);
        $expenseGrowth = $this->percentageGrowth($current['expense'], $previous['expense']);
        $profitGrowth  = $this->percentageGrowth($current['profit'], $previous['profit']);

        return [
            'revenue_growth'  => $revenueGrowth,
            'expense_growth'  => $expenseGrowth,
            'profit_growth'   => $profitGrowth,
            'is_profit_up'    => $profitGrowth['value'] >= 0,
        ];
    }

    private function percentageGrowth(float $current, float $previous): array
    {
        if ($previous == 0) {
            return ['value' => 0, 'percentage' => 0];
        }

        $diff = $current - $previous;
        $percentage = round(($diff / $previous) * 100, 2);

        return [
            'value'      => round($diff, 2),
            'percentage' => $percentage,
            'direction'  => $diff >= 0 ? 'up' : 'down',
        ];
    }

    // ── Top Selling Products ────────────────────────────
    private function getTopSellingProducts(string $startDate, string $endDate, int $limit = 5): array
    {
        $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('id');

        $topProducts = OrderDetail::whereIn('order_id', $deliveredOrderIds)
            ->select('product_id', 'variation_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->selectRaw('SUM(total) as total_sales')
            ->groupBy('product_id', 'variation_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->with(['product', 'variation'])
            ->get()
            ->map(function ($item) {
                $name = $item->product->title ?? 'Unknown';

                if ($item->variation_id && $item->variation) {
                    $varName = $item->variation->attributes
                        ->map(fn($attr) => $attr->attributeValue->name ?? '')
                        ->filter()
                        ->join(', ');
                    $name .= $varName ? " ({$varName})" : '';
                }

                return [
                    'product_id'   => $item->product_id,
                    'variation_id' => $item->variation_id,
                    'name'         => $name,
                    'total_sold'   => (int) $item->total_sold,
                    'total_sales'  => (float) $item->total_sales,
                    'image'        => $item->variation?->image ?? $item->product?->thumbnail,
                ];
            });

        return $topProducts->toArray();
    }

    // ── Sales Trend (Daily) ─────────────────────────────
    private function getSalesTrend(string $startDate, string $endDate): array
    {
        $sales = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->select(DB::raw('DATE(order_date) as date'))
            ->selectRaw('SUM(grand_total) as revenue')
            ->selectRaw('COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date'         => $item->date,
                'revenue'      => (float) $item->revenue,
                'orders_count' => (int) $item->orders_count,
            ]);

        return $sales->toArray();
    }

    // ── Sales by Category ───────────────────────────────
    private function getSalesByCategory(string $startDate, string $endDate): array
    {
        $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('id');

        $orderDetails = OrderDetail::whereIn('order_id', $deliveredOrderIds)
            ->with('product')
            ->get();

        // ── Step 1: সব unique mega_category ids একবারে collect করো ──
        $allIds = $orderDetails->flatMap(function ($detail) {
            $ids = is_string($detail->product->mega_category_ids)
                ? json_decode($detail->product->mega_category_ids, true)
                : ($detail->product->mega_category_ids ?? []);
            return $ids;
        })->unique()->filter();

        // ── Step 2: category fetch  ──
        $megaCategories = \App\Models\MegaCategory::whereIn('id', $allIds)
            ->pluck('name', 'id');

        // ── Step 3: Category wise amount sum করো ──
        $categories = [];

        foreach ($orderDetails as $detail) {
            $megaCategoryIds = is_string($detail->product->mega_category_ids)
                ? json_decode($detail->product->mega_category_ids, true)
                : ($detail->product->mega_category_ids ?? []);

            if (empty($megaCategoryIds)) {
                $categoryName = 'Uncategorized';
            } else {
                $firstId = is_array($megaCategoryIds) ? $megaCategoryIds[0] : $megaCategoryIds;
                $categoryName = $megaCategories->get($firstId) ?? 'Unknown';
            }

            $categories[$categoryName] = ($categories[$categoryName] ?? 0) + $detail->total;
        }

        return collect($categories)
            ->map(fn($amount, $name) => ['category' => $name, 'amount' => round($amount, 2)])
            ->sortByDesc('amount')
            ->values()
            ->toArray();
    }


    // ── Purchase Price Resolve ──────────────────────────
    private function resolvePurchasePrice(int $productId, ?int $variationId): float
    {
        if ($variationId) {
            $lastPurchase = PurchaseDetail::where('variation_id', $variationId)
                ->orderByDesc('created_at')
                ->value('purchase_price');

            if ($lastPurchase) return (float) $lastPurchase;

            return (float) (ProductVariation::find($variationId)?->purchase_price ?? 0);
        }

        $lastPurchase = PurchaseDetail::where('product_id', $productId)
            ->whereNull('variation_id')
            ->orderByDesc('created_at')
            ->value('purchase_price');

        if ($lastPurchase) return (float) $lastPurchase;

        return (float) (Product::find($productId)?->purchase_price ?? 0);
    }

    // ── Date Range Resolver ─────────────────────────────
    private function resolveDateRange(string $period): array
    {
        $endDate = Carbon::today()->toDateString();

        switch ($period) {
            case 'today':
                $startDate     = $endDate;
                $prevEndDate   = Carbon::yesterday()->toDateString();
                $prevStartDate = $prevEndDate;
                break;

            case 'yesterday':
                $startDate     = Carbon::yesterday()->toDateString();
                $endDate       = $startDate;
                $prevEndDate   = Carbon::yesterday()->subDay()->toDateString();
                $prevStartDate = $prevEndDate;
                break;

            case 'last_7_days':
                $startDate     = Carbon::today()->subDays(6)->toDateString();
                $prevEndDate   = Carbon::today()->subDays(7)->toDateString();
                $prevStartDate = Carbon::today()->subDays(13)->toDateString();
                break;

            case 'last_30_days':
            default:
                $startDate     = Carbon::today()->subDays(29)->toDateString();
                $prevEndDate   = Carbon::today()->subDays(30)->toDateString();
                $prevStartDate = Carbon::today()->subDays(59)->toDateString();
                break;

            case 'this_month':
                $startDate     = Carbon::now()->startOfMonth()->toDateString();
                $endDate       = Carbon::now()->endOfMonth()->toDateString();
                $prevStartDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $prevEndDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;

            case 'last_month':
                $startDate     = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endDate       = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                $prevStartDate = Carbon::now()->subMonths(2)->startOfMonth()->toDateString();
                $prevEndDate   = Carbon::now()->subMonths(2)->endOfMonth()->toDateString();
                break;
        }

        return [$startDate, $endDate, $prevStartDate, $prevEndDate];
    }

    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today'        => 'Today',
            'yesterday'    => 'Yesterday',
            'last_7_days'  => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'this_month'   => 'This Month',
            'last_month'   => 'Last Month',
            default        => 'Last 30 Days',
        };
    }
}

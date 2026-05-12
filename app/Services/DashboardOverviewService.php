<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Purchase;
use App\Models\TransactionExpense;
use App\Models\TransactionIncome;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardOverviewService
{
    private array $deliveredStatuses = [Status::Delivered->value];
    private array $returnedStatuses  = [
        Status::ReturntoCourier->value,
        Status::ReturnReceived->value,
        Status::ReturnRequest->value,
    ];

    public function generate(string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        if ($period === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->toDateString();
            $endDate   = Carbon::parse($customEnd)->toDateString();
        } else {
            [$startDate, $endDate] = $this->resolveDateRange($period);
        }
        return [
            'period'           => ['start' => $startDate, 'end' => $endDate, 'label' => $period],
            'products'         => $this->getProductStats(),
            'orders'           => $this->getOrderStats($startDate, $endDate),
            'employees'        => $this->getEmployeeStats(),
            'accounts'         => $this->getAccountStats($startDate, $endDate),
            'charts'           => $this->getChartData(),
            'top_products'     => $this->getTopProducts($startDate, $endDate),
            'loyal_customers'  => $this->getLoyalCustomers(),
            'latest_sales'     => $this->getLatestSales(),
            'low_stock'        => $this->getLowStockProducts(),
        ];
    }

    // ══════════════════════════════════════════
    //  1. PRODUCTS
    // ══════════════════════════════════════════
    private function getProductStats(): array
    {
        $lowStockThreshold = 10;

        // Single products
        $singles = Product::where('type', 'single')->get();

        $singleTotal    = $singles->count();
        $singleActive   = $singles->where('status', Status::Active->value)->count();
        $singleInactive = $singles->where('status', Status::Inactive->value)->count();

        // Stock from warehouse_info JSON
        $singleOutOfStock = $singles->filter(function ($p) {
            $stock = collect($p->warehouse_info ?? [])->sum('quantity');
            return $stock <= 0;
        })->count();

        $singleLowStock = $singles->filter(function ($p) use ($lowStockThreshold) {
            $stock = collect($p->warehouse_info ?? [])->sum('quantity');
            return $stock > 0 && $stock < $lowStockThreshold;
        })->count();

        // Variable products (via product_variation_stocks)
        $variationColumn = $this->getVariationColumnName();

        $variationStocks = DB::table('product_variation_stocks as pvs')
            ->join('product_variations as pv', "pvs.{$variationColumn}", '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select('pv.id', 'p.status', DB::raw('SUM(pvs.quantity) as total_stock'))
            ->groupBy('pv.id', 'p.status')
            ->get();

        $variableTotal    = DB::table('products')->where('type', 'variable')->count();
        $variableActive   = DB::table('products')->where('type', 'variable')->where('status', Status::Active->value)->count();
        $variableInactive = DB::table('products')->where('type', 'variable')->where('status', Status::Inactive->value)->count();

        $variableOutOfStock = $variationStocks->where('total_stock', '<=', 0)->count();
        $variableLowStock   = $variationStocks->filter(fn($v) => $v->total_stock > 0 && $v->total_stock < $lowStockThreshold)->count();

        return [
            'total'        => $singleTotal + $variableTotal,
            'active'       => $singleActive + $variableActive,
            'inactive'     => $singleInactive + $variableInactive,
            'out_of_stock' => $singleOutOfStock + $variableOutOfStock,
            'low_stock'    => $singleLowStock + $variableLowStock,
        ];
    }

    // ══════════════════════════════════════════
    //  2. ORDERS
    // ══════════════════════════════════════════
    private function getOrderStats(string $startDate, string $endDate): array
    {
        // Fetch both count and the sum of grand_total
        $stats = Order::whereBetween('order_date', [$startDate, $endDate])
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(grand_total) as amount')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Helper functions to keep the array clean
        $getCount = fn($status) => (int) ($stats[$status]->count ?? 0);
        $getAmount = fn($status) => (float) ($stats[$status]->amount ?? 0);

        return [
            'pending' => [
                'count'  => $getCount(Status::Pending->value),
                'amount' => $getAmount(Status::Pending->value),
            ],
            'shipped' => [
                'count'  => $getCount(Status::Shipped->value)
                    + $getCount(Status::HandovertoCourier->value)
                    + $getCount(Status::InTransit->value),
                'amount' => $getAmount(Status::Shipped->value)
                    + $getAmount(Status::HandovertoCourier->value)
                    + $getAmount(Status::InTransit->value),
            ],
            'delivered' => [
                'count'  => $getCount(Status::Delivered->value),
                'amount' => $getAmount(Status::Delivered->value),
            ],
            'cancelled' => [
                'count'  => $getCount(Status::Cancelled->value),
                'amount' => $getAmount(Status::Cancelled->value),
            ],
            'return_request' => [
                'count'  => $getCount(Status::ReturnRequest->value),
                'amount' => $getAmount(Status::ReturnRequest->value),
            ],
        ];
    }

    // ══════════════════════════════════════════
    //  3. EMPLOYEES
    // ══════════════════════════════════════════
    private function getEmployeeStats(): array
    {
        $today    = Carbon::today()->toDateString();
        $month    = Carbon::now()->month;
        $year     = Carbon::now()->year;

        $total = Employee::count();

        // Today attendance
        $todayAttendance = Attendance::whereDate('date', $today)
            ->select('status', 'is_late', DB::raw('COUNT(*) as count'))
            ->groupBy('status', 'is_late')
            ->get();

        $absent = Attendance::whereDate('date', $today)
            ->where('status', Attendance::STATUS_ABSENT)
            ->count();

        $late = Attendance::whereDate('date', $today)
            ->where('is_late', 1)
            ->count();

        // Leave requests this month (status = leave/holiday)
        $leaveRequests = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', Attendance::STATUS_HOLIDAY)
            ->distinct('employee_id')
            ->count('employee_id');

        // Salary Due — employees with addition salary configured
        // (যাদের salary setup আছে তারাই due এ আসবে, payment table না থাকলে এভাবে count করা যায়)
        $salaryDue = EmployeeSalary::where('type', 'addition')
            ->distinct('employee_id')
            ->count('employee_id');

        return [
            'total'         => $total,
            'late'          => $late,
            'absent'        => $absent,
            'leave_request' => $leaveRequests,
            'salary_due'    => $salaryDue,
        ];
    }



    // ══════════════════════════════════════════
    //  5. ACCOUNTS
    // ══════════════════════════════════════════
    private function getAccountStats(string $startDate, string $endDate): array
    {
        // Revenue — delivered orders
        $revenue = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->sum('grand_total');

        // Due — grand_total - payment_amount (delivered orders)
        $due = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->selectRaw('SUM(grand_total - payment_amount) as due')
            ->value('due') ?? 0;

        // Expense — approved transaction expenses
        $expense = TransactionExpense::whereBetween('date', [$startDate, $endDate])
            ->where('status', Status::Approved->value)
            ->sum('total_amount');

        // Purchase cost
        $purchaseCost = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', Status::Completed->value)
            ->sum('grand_total');


        $totalExpense = (float) $expense + (float) $purchaseCost;
        $profit       = (float) $revenue - $totalExpense;

        return [
            'revenue'       => round((float) $revenue, 2),
            'expense'       => round($totalExpense, 2),
            'profit'        => round($profit, 2),
            'due'           => round((float) $due, 2),
        ];
    }

    // ══════════════════════════════════════════
    //  6. CHARTS
    // ══════════════════════════════════════════
    private function getChartData(): array
    {
        // Last 7 months sales vs purchase vs profit
        $months = collect(range(6, 0))->map(fn($i) => [
            'month' => Carbon::now()->subMonths($i)->format('M'),
            'start' => Carbon::now()->subMonths($i)->startOfMonth()->toDateString(),
            'end'   => Carbon::now()->subMonths($i)->endOfMonth()->toDateString(),
        ]);

        $salesPurchaseProfit = $months->map(function ($m) {
            $sales    = (float) Order::whereBetween('order_date', [$m['start'], $m['end']])
                ->whereIn('status', $this->deliveredStatuses)
                ->sum('grand_total');

            $purchase = (float) Purchase::whereBetween('purchase_date', [$m['start'], $m['end']])
                ->where('status', Status::Completed->value)
                ->sum('grand_total');

            return [
                'month'    => $m['month'],
                'sales'    => round($sales, 2),
                'purchase' => round($purchase, 2),
                'profit'   => round($sales - $purchase, 2),
            ];
        })->values()->toArray();

        // Order source breakdown
        $orderSourceCounts = Order::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');

        $orderSource = [
            ['name' => 'POS',     'value' => (int) ($orderSourceCounts['pos']     ?? 0)],
            ['name' => 'Website', 'value' => (int) ($orderSourceCounts['sales']   ?? 0)],
            ['name' => 'Landing', 'value' => (int) ($orderSourceCounts['landing'] ?? 0)],
        ];

        // Order status breakdown
        $orderStatusCounts = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $orderStatus = [
            ['name' => 'Delivered',  'value' => (int) ($orderStatusCounts[Status::Delivered->value]     ?? 0)],
            ['name' => 'Pending',    'value' => (int) ($orderStatusCounts[Status::Pending->value]       ?? 0)],
            ['name' => 'Shipped',    'value' => (int) ($orderStatusCounts[Status::Shipped->value]       ?? 0)],
            ['name' => 'Cancelled',  'value' => (int) ($orderStatusCounts[Status::Cancelled->value]     ?? 0)],
            ['name' => 'Returned',   'value' => (int) ($orderStatusCounts[Status::ReturnRequest->value] ?? 0)],
        ];

        // Inventory status
        $variationReceived = (int) DB::table('product_variation_stocks')->sum('quantity');

        $singleReceived = (int) Product::where('type', 'single')
            ->get()
            ->sum(fn($p) => collect($p->warehouse_info ?? [])->sum('quantity'));

        $totalReceived = $singleReceived + $variationReceived;

        $deliveredOrderIds = Order::whereIn('status', $this->deliveredStatuses)->pluck('id');

        $shipped = (int) OrderDetail::whereIn('order_id', $deliveredOrderIds)->sum('quantity');

        $holdStatuses = [
            Status::Pending->value,
            Status::Approved->value,
            Status::Confirmed->value,
            Status::Processing->value,
            Status::Shipped->value,
            Status::ReadyToShipped->value,
            Status::HandovertoCourier->value,
            Status::InTransit->value,
            Status::Hold->value,
            Status::Waiting->value,
            Status::Draft->value,
        ];

        $holdOrderIds = Order::whereIn('status', $holdStatuses)->pluck('id');

        $inventoryStatus = [
            ['name' => 'Received', 'value' => $totalReceived],
            ['name' => 'Hold',     'value' => (int) OrderDetail::whereIn('order_id', $holdOrderIds)->sum('quantity')],
            ['name' => 'Shipped',  'value' => $shipped],
        ];

        return [
            'sales_purchase_profit' => $salesPurchaseProfit,
            'order_source'          => $orderSource,
            'order_status'          => $orderStatus,
            'inventory_status'      => $inventoryStatus,
        ];
    }

    // ══════════════════════════════════════════
    //  7. TOP 5 PRODUCTS
    // ══════════════════════════════════════════
    private function getTopProducts(string $startDate, string $endDate): array
    {
        $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('id');

        return OrderDetail::whereIn('order_id', $deliveredOrderIds)
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->with('product:id,title,thumbnail')
            ->get()
            ->map(fn($row) => [
                'product_id'    => $row->product_id,
                'product_name'  => $row->product->title ?? 'Unknown',
                'thumbnail'     => $row->product->thumbnail ?? null,
                'total_sold'    => (int) $row->total_sold,
                'total_revenue' => round((float) $row->total_revenue, 2),
            ])
            ->toArray();
    }

    // ══════════════════════════════════════════
    //  8. TOP 5 LOYAL CUSTOMERS
    // ══════════════════════════════════════════
    private function getLoyalCustomers(): array
    {
        return Order::whereIn('status', $this->deliveredStatuses)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(grand_total) as lifetime_spent'))
            ->groupBy('customer_id')
            ->orderByDesc('lifetime_spent')
            ->limit(5)
            ->with('customer:id,name,phone')
            ->get()
            ->map(fn($row) => [
                'customer_id'   => $row->customer_id,
                'name'          => $row->customer->name  ?? 'Unknown',
                'phone'         => $row->customer->phone ?? '-',
                'total_orders'  => (int) $row->total_orders,
                'lifetime_spent' => round((float) $row->lifetime_spent, 2),
            ])
            ->toArray();
    }

    // ══════════════════════════════════════════
    //  9. LATEST 5 SALES
    // ══════════════════════════════════════════
    private function getLatestSales(): array
    {
        return Order::with('customer:id,name')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn($order) => [
                'id'            => $order->id,
                'order_no'      => $order->order_no,
                'customer_name' => $order->customer->name ?? 'Walk-in',
                'grand_total'   => (float) $order->grand_total,
                'status'        => $order->status,
                'order_date'    => $order->order_date,
            ])
            ->toArray();
    }

    // ══════════════════════════════════════════
    //  10. LOW STOCK (all warehouses)
    // ══════════════════════════════════════════
    private function getLowStockProducts(): array
    {
        $threshold = 10;
        $result    = [];

        // Single products
        Product::where('type', 'single')->get()->each(function ($p) use ($threshold, &$result) {
            $stock = collect($p->warehouse_info ?? [])->sum('quantity');
            if ($stock > 0 && $stock < $threshold) {
                $result[] = [
                    'product_id'    => $p->id,
                    'product_name'  => $p->title,
                    'variation_name' => null,
                    'sku'           => $p->sku_code ?? null,
                    'current_stock' => (int) $stock,
                    'threshold'     => $threshold,
                ];
            }
        });

        // Variation products
        $variationColumn = $this->getVariationColumnName();

        DB::table('product_variation_stocks as pvs')
            ->join('product_variations as pv', "pvs.{$variationColumn}", '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select('pv.id as variation_id', 'pv.product_id', 'pv.sku', 'p.title as product_name', DB::raw('SUM(pvs.quantity) as total_stock'))
            ->groupBy('pv.id', 'pv.product_id', 'pv.sku', 'p.title')
            ->havingRaw('total_stock > 0 AND total_stock < ?', [$threshold])
            ->get()
            ->each(function ($row) use ($threshold, &$result) {
                $result[] = [
                    'product_id'    => $row->product_id,
                    'product_name'  => $row->product_name,
                    'variation_name' => $this->getVariationName($row->variation_id),
                    'sku'           => $row->sku ?? null,
                    'current_stock' => (int) $row->total_stock,
                    'threshold'     => $threshold,
                ];
            });

        // Sort by stock ascending, take 5
        usort($result, fn($a, $b) => $a['current_stock'] <=> $b['current_stock']);

        return array_slice($result, 0, 5);
    }

    // ══════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════
    private function resolveDateRange(string $period): array
    {
        return match ($period) {
            'today'       => [Carbon::today()->toDateString(), Carbon::today()->toDateString()],
            'yesterday'   => [Carbon::yesterday()->toDateString(), Carbon::yesterday()->toDateString()],
            'last_7_days' => [Carbon::today()->subDays(6)->toDateString(), Carbon::today()->toDateString()],
            'last_month'  => [
                Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                Carbon::now()->subMonth()->endOfMonth()->toDateString(),
            ],
            default => [ // this_month
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->toDateString(),
            ],
        };
    }

    private function getVariationColumnName(): string
    {
        return DB::getSchemaBuilder()->hasColumn('product_variation_stocks', 'product_variation_id')
            ? 'product_variation_id'
            : 'variation_id';
    }

    private function getVariationName(int $variationId): ?string
    {
        $variation = \App\Models\ProductVariation::with(
            'attributes.attributeGroup',
            'attributes.attributeValue'
        )->find($variationId);

        if (!$variation?->attributes) return null;

        return $variation->attributes
            ->filter(fn($a) => $a->attributeGroup && $a->attributeValue)
            ->map(fn($a) => "{$a->attributeGroup->name}: {$a->attributeValue->name}")
            ->join(', ');
    }
}

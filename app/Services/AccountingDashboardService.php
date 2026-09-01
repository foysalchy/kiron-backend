<?php

namespace App\Services;

use App\Models\AccountGroup;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\Order;
use App\Models\Party;
use App\Models\TransactionJournal;
use App\Models\TransactionJournalAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingDashboardService
{
    /**
     * Resolve date range from period or custom params
     */
    private function resolveDateRange(int $companyId, string $period, ?string $customStart, ?string $customEnd): array
    {
        $setting = AutoAccountingService::getSetting($companyId);
        $fyStart = $setting->financial_year_start ? Carbon::parse($setting->financial_year_start)->format('Y-m-d') : Carbon::now()->startOfYear()->format('Y-m-d');
        $fyEnd = $setting->financial_year_end ? Carbon::parse($setting->financial_year_end)->format('Y-m-d') : Carbon::now()->endOfYear()->format('Y-m-d');

        switch ($period) {
            case 'today':
                $start = Carbon::today()->format('Y-m-d');
                $end = Carbon::today()->format('Y-m-d');
                break;
            case 'this_month':
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_fy':
                $start = $fyStart;
                $end = $fyEnd;
                break;
            case 'custom':
                $start = $customStart ?: Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = $customEnd ?: Carbon::now()->format('Y-m-d');
                break;
            default:
                $start = Carbon::now()->startOfMonth()->format('Y-m-d');
                $end = Carbon::now()->format('Y-m-d');
                break;
        }

        return [
            'start' => $start,
            'end'   => $end,
            'fy_title' => $setting->financial_year_title ?? 'Current FY',
            'fy_start' => $fyStart,
            'fy_end'   => $fyEnd,
        ];
    }

    /**
     * 1. High-Level & Banner KPI Stats
     */
    public function getDashboardStats(int $companyId, string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $range = $this->resolveDateRange($companyId, $period, $customStart, $customEnd);
        $start = $range['start'];
        $end = $range['end'];

        // --- 1. REVENUE & INCOME ---
        $incomeAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Income');
        })->where('company_id', $companyId)->get();

        $totalRevenue = 0;
        $posRevenue = 0;
        $webRevenue = 0;
        $deliveryIncome = 0;
        $otherIncome = 0;

        foreach ($incomeAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $start, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('credit') - $j->sum('debit'));
            if ($amount != 0) {
                $totalRevenue += $amount;
                $nameLower = strtolower($acc->name);
                if (str_contains($nameLower, 'pos')) {
                    $posRevenue += $amount;
                } elseif (str_contains($nameLower, 'web') || str_contains($nameLower, 'online') || str_contains($nameLower, 'ecommerce')) {
                    $webRevenue += $amount;
                } elseif (str_contains($nameLower, 'delivery') || str_contains($nameLower, 'shipping')) {
                    $deliveryIncome += $amount;
                } else {
                    $otherIncome += $amount;
                }
            }
        }

        // --- 2. DIRECT COGS & PURCHASES ---
        $cogsAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('name', 'like', '%Direct%')->orWhere('name', 'like', '%COGS%');
        })->where('company_id', $companyId)->get();

        $totalCogs = 0;
        $totalPurchases = 0;
        foreach ($cogsAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $start, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('debit') - $j->sum('credit'));
            if ($amount != 0) {
                $totalCogs += $amount;
                if (str_contains(strtolower($acc->name), 'purchase')) {
                    $totalPurchases += $amount;
                }
            }
        }
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // --- 3. OPERATING & INDIRECT EXPENSES ---
        $expenseAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Expense')->where('name', 'not like', '%Direct%');
        })->where('company_id', $companyId)->get();

        $totalOperatingExpense = 0;
        $payrollExpense = 0;
        $adminExpense = 0;
        foreach ($expenseAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $start, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('debit') - $j->sum('credit'));
            if ($amount != 0) {
                $totalOperatingExpense += $amount;
                $nameLower = strtolower($acc->name);
                if (str_contains($nameLower, 'salary') || str_contains($nameLower, 'payroll') || str_contains($nameLower, 'wages')) {
                    $payrollExpense += $amount;
                } else {
                    $adminExpense += $amount;
                }
            }
        }
        $netProfit = $grossProfit - $totalOperatingExpense;
        $netMarginPct = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // --- 4. LIQUIDITY & WORKING CAPITAL (As of Current / Range End) ---
        $cashAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('name', 'like', '%Cash%');
        })->where('company_id', $companyId)->get();

        $cashInHand = 0;
        foreach ($cashAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();
            $bal = (float)($j->sum('debit') - $j->sum('credit'));
            $cashInHand += max(0, $bal);
        }

        $bankAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('name', 'like', '%Bank%');
        })->where('company_id', $companyId)->get();

        $bankBalance = 0;
        foreach ($bankAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();
            $bal = (float)($j->sum('debit') - $j->sum('credit'));
            $bankBalance += max(0, $bal);
        }
        $totalLiquidity = $cashInHand + $bankBalance;

        // Debtors (Receivable) & Creditors (Payable)
        $accountsReceivable = (float)Party::where('company_id', $companyId)->where('type', Party::TYPE_CUSTOMER)->sum('due_amount');
        $accountsPayable = (float)Party::where('company_id', $companyId)->where('type', Party::TYPE_SUPPLIER)->sum('due_amount');

        // Total Assets & Liabilities
        $allAssetAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Assets');
        })->where('company_id', $companyId)->get();

        $totalAssets = 0;
        foreach ($allAssetAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();
            $bal = (float)($j->sum('debit') - $j->sum('credit'));
            if ($bal > 0) $totalAssets += $bal;
        }

        $allLiabilityAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Liabilities');
        })->where('company_id', $companyId)->get();

        $totalLiabilities = 0;
        foreach ($allLiabilityAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $end) {
                $q->where('company_id', $companyId)->whereDate('date', '<=', $end);
            })->where('chart_of_account_id', $acc->id)->get();
            $bal = (float)($j->sum('credit') - $j->sum('debit'));
            if ($bal > 0) $totalLiabilities += $bal;
        }

        // Voucher Counts
        $totalVouchers = TransactionJournal::where('company_id', $companyId)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->count();

        $todayVouchers = TransactionJournal::where('company_id', $companyId)
            ->whereDate('date', Carbon::today()->format('Y-m-d'))
            ->count();

        return [
            'period'                => $period,
            'start_date'            => $start,
            'end_date'              => $end,
            'financial_year_title'  => $range['fy_title'],
            'total_revenue'         => round($totalRevenue, 2),
            'pos_revenue'           => round($posRevenue, 2),
            'web_revenue'           => round($webRevenue, 2),
            'delivery_income'       => round($deliveryIncome, 2),
            'other_income'          => round($otherIncome, 2),
            'total_cogs'            => round($totalCogs, 2),
            'total_purchases'       => round($totalPurchases ?: $totalCogs, 2),
            'gross_profit'          => round($grossProfit, 2),
            'gross_margin_pct'      => $grossMarginPct,
            'total_operating_expense' => round($totalOperatingExpense, 2),
            'payroll_expense'       => round($payrollExpense, 2),
            'admin_expense'         => round($adminExpense, 2),
            'net_profit'            => round($netProfit, 2),
            'net_margin_pct'        => $netMarginPct,
            'is_profit'             => $netProfit >= 0,
            'cash_in_hand'          => round($cashInHand, 2),
            'bank_balance'          => round($bankBalance, 2),
            'total_liquidity'       => round($totalLiquidity, 2),
            'accounts_receivable'   => round($accountsReceivable, 2),
            'accounts_payable'      => round($accountsPayable, 2),
            'total_assets'          => round($totalAssets, 2),
            'total_liabilities'     => round($totalLiabilities, 2),
            'total_vouchers'        => $totalVouchers,
            'today_vouchers'        => $todayVouchers,
        ];
    }

    /**
     * 2. Financial Dashboard Charts Data (Trend & Category Breakdowns)
     */
    public function getDashboardCharts(int $companyId): array
    {
        // Monthly Trend (Last 6 Months)
        $monthlyTrend = [];
        $cashflowTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('M Y');
            $mStart = $monthDate->copy()->startOfMonth()->format('Y-m-d');
            $mEnd = $monthDate->copy()->endOfMonth()->format('Y-m-d');

            // Income
            $income = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $mStart, $mEnd) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $mStart)->whereDate('date', '<=', $mEnd);
            })->whereHas('chartOfAccount.accountGroup', fn($g) => $g->where('account_type', 'Income'))
            ->selectRaw('COALESCE(SUM(credit - debit), 0) as total')
            ->value('total');

            // Expense (Direct + Indirect)
            $expense = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $mStart, $mEnd) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $mStart)->whereDate('date', '<=', $mEnd);
            })->whereHas('chartOfAccount.accountGroup', fn($g) => $g->where('account_type', 'Expense'))
            ->selectRaw('COALESCE(SUM(debit - credit), 0) as total')
            ->value('total');

            $profit = $income - $expense;

            // Cash Inflow & Outflow (Cash & Bank ledger movements)
            $inflow = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $mStart, $mEnd) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $mStart)->whereDate('date', '<=', $mEnd);
            })->whereHas('chartOfAccount.accountGroup', fn($g) => $g->where('name', 'like', '%Cash%')->orWhere('name', 'like', '%Bank%'))
            ->sum('debit');

            $outflow = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $mStart, $mEnd) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $mStart)->whereDate('date', '<=', $mEnd);
            })->whereHas('chartOfAccount.accountGroup', fn($g) => $g->where('name', 'like', '%Cash%')->orWhere('name', 'like', '%Bank%'))
            ->sum('credit');

            $monthlyTrend[] = [
                'month'   => $monthKey,
                'income'  => round(max(0, $income), 2),
                'expense' => round(max(0, $expense), 2),
                'profit'  => round($profit, 2),
            ];

            $cashflowTrend[] = [
                'month'   => $monthKey,
                'inflow'  => round($inflow, 2),
                'outflow' => round($outflow, 2),
                'net'     => round($inflow - $outflow, 2),
            ];
        }

        // Revenue Breakdown by Account / Source
        $revenueByCategory = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Income');
        })->where('company_id', $companyId)
        ->get()
        ->map(function ($acc) use ($companyId) {
            $amt = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('chart_of_account_id', $acc->id)
            ->selectRaw('COALESCE(SUM(credit - debit), 0) as total')
            ->value('total');

            return [
                'name'  => $acc->name,
                'value' => round(max(0, $amt), 2),
            ];
        })->filter(fn($i) => $i['value'] > 0)->values();

        // Expense Breakdown by Account Group
        $expenseByCategory = AccountGroup::where('company_id', $companyId)
            ->where('account_type', 'Expense')
            ->get()
            ->map(function ($grp) use ($companyId) {
                $amt = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereHas('chartOfAccount', fn($cq) => $cq->where('account_group_id', $grp->id))
                ->selectRaw('COALESCE(SUM(debit - credit), 0) as total')
                ->value('total');

                return [
                    'name'  => $grp->name,
                    'value' => round(max(0, $amt), 2),
                ];
            })->filter(fn($i) => $i['value'] > 0)->values();

        return [
            'monthly_trend'        => $monthlyTrend,
            'cashflow_trend'       => $cashflowTrend,
            'revenue_by_category'  => $revenueByCategory,
            'expense_by_category'  => $expenseByCategory,
        ];
    }

    /**
     * 3. Recent Vouchers & Active Liquid Accounts
     */
    public function getRecentActivity(int $companyId): array
    {
        // Recent 6 Double-Entry Vouchers
        $recentVouchers = TransactionJournal::with([
            'accounts.chartOfAccount.accountGroup',
            'party:id,name,phone',
            'creator:id,name'
        ])
        ->where('company_id', $companyId)
        ->orderByDesc('date')
        ->orderByDesc('id')
        ->limit(6)
        ->get();

        // Active Liquid Asset Ledgers (Cash, Bank, Wallet)
        $liquidAccounts = ChartOfAccount::with('accountGroup')
            ->whereHas('accountGroup', function ($q) {
                $q->where('name', 'like', '%Cash%')
                  ->orWhere('name', 'like', '%Bank%')
                  ->orWhere('name', 'like', '%Current Assets%');
            })
            ->where('company_id', $companyId)
            ->get()
            ->map(function ($acc) use ($companyId) {
                $dr = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('chart_of_account_id', $acc->id)->sum('debit');

                $cr = (float)TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('chart_of_account_id', $acc->id)->sum('credit');

                $bal = $dr - $cr;

                return [
                    'id'            => $acc->id,
                    'name'          => $acc->name,
                    'group_name'    => $acc->accountGroup?->name ?? 'General',
                    'balance'       => round($bal, 2),
                    'balance_type'  => $bal >= 0 ? 'Dr' : 'Cr',
                ];
            })
            ->filter(fn($a) => $a['balance'] != 0)
            ->values();

        return [
            'recent_vouchers' => $recentVouchers,
            'liquid_accounts' => $liquidAccounts,
        ];
    }

    /**
     * 4. Purchase Price Resolver Helper
     */
    private function resolvePurchasePrice(int $productId, ?int $variationId): float
    {
        if ($variationId) {
            $lastPurchase = \App\Models\PurchaseDetail::where('variation_id', $variationId)
                ->orderByDesc('created_at')
                ->value('purchase_price');

            if ($lastPurchase) return (float) $lastPurchase;

            return (float) (\App\Models\ProductVariation::find($variationId)?->purchase_price ?? 0);
        }

        $lastPurchase = \App\Models\PurchaseDetail::where('product_id', $productId)
            ->whereNull('variation_id')
            ->orderByDesc('created_at')
            ->value('purchase_price');

        if ($lastPurchase) return (float) $lastPurchase;

        return (float) (\App\Models\Product::find($productId)?->purchase_price ?? 0);
    }

    /**
     * 5. Order Profitability & Unit Economics Breakdown
     */
    public function getOrderProfitabilityAnalysis(int $companyId, string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $range = $this->resolveDateRange($companyId, $period, $customStart, $customEnd);
        $start = $range['start'];
        $end = $range['end'];

        $orders = \App\Models\Order::with(['orderDetails.product', 'orderDetails.variation'])
            ->where('company_id', $companyId)
            ->whereBetween('order_date', [$start, $end])
            ->get();

        $totalOrders = $orders->count();
        $totalUnitsSold = 0;
        $totalSellingPrice = 0;
        $totalShippingRevenue = 0;
        $totalPurchaseCost = 0;
        $totalCourierDeliveryCost = 0;
        $courierDeliveryOrdersCount = 0;

        foreach ($orders as $order) {
            $totalSellingPrice += (float)$order->subtotal;
            $totalShippingRevenue += (float)$order->other_charges;

            foreach ($order->orderDetails as $detail) {
                $qty = (int)$detail->quantity;
                $totalUnitsSold += $qty;
                $buyPrice = $this->resolvePurchasePrice($detail->product_id, $detail->variation_id);
                $totalPurchaseCost += ($buyPrice * $qty);
            }

            // Courier Delivery Cost for Shipped/Delivered orders
            if (!in_array($order->status, [
                \App\Enums\Status::Cancelled->value,
                \App\Enums\Status::Returned->value,
                \App\Enums\Status::ReturnRequest->value,
                \App\Enums\Status::ReturntoCourier->value,
            ])) {
                $cCost = 0;
                if (!empty($order->courier_info)) {
                    $cInfo = is_array($order->courier_info) ? $order->courier_info : json_decode($order->courier_info, true);
                    $cCost = (float)($cInfo['courier_charge'] ?? $cInfo['delivery_charge'] ?? $cInfo['delivery_fee'] ?? 0);
                }
                if ($cCost > 0) {
                    $totalCourierDeliveryCost += $cCost;
                    $courierDeliveryOrdersCount++;
                }
            }
        }

        // Gross Profit from Orders
        $orderGrossProfit = ($totalSellingPrice + $totalShippingRevenue) - $totalPurchaseCost;

        // Find courier expense account ID to avoid double-counting in other expenses
        $setting = AccountingSetting::withoutGlobalScopes()->where('company_id', $companyId)->first();
        $courierExpenseAccountId = $setting?->default_courier_expense_account_id;

        // Other Operating & Indirect Expenses in this period (excluding COGS and Courier Account if counted above)
        $otherExpensesQuery = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $start, $end) {
            $q->where('company_id', $companyId)->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
        })->whereHas('chartOfAccount.accountGroup', fn($g) => $g->where('account_type', 'Expense')->where('name', 'not like', '%Direct%'));

        if ($courierExpenseAccountId && $totalCourierDeliveryCost > 0) {
            $otherExpensesQuery->where('chart_of_account_id', '!=', $courierExpenseAccountId);
        }

        $otherExpenses = (float)$otherExpensesQuery
            ->selectRaw('COALESCE(SUM(debit - credit), 0) as total')
            ->value('total');

        // Courier Delivery & Return Loss from Cancelled/Returned orders in this period
        $cancelledCourierOrders = \App\Models\Order::where('company_id', $companyId)
            ->whereBetween('order_date', [$start, $end])
            ->whereIn('status', [
                \App\Enums\Status::Cancelled->value,
                \App\Enums\Status::Returned->value,
                \App\Enums\Status::ReturnRequest->value,
                \App\Enums\Status::ReturntoCourier->value,
            ])
            ->get();

        $returnedOrdersCount = $cancelledCourierOrders->count();
        $courierReturnLoss = 0;
        foreach ($cancelledCourierOrders as $co) {
            $deliveryFee = 0;
            if (!empty($co->courier_info)) {
                $info = is_array($co->courier_info) ? $co->courier_info : json_decode($co->courier_info, true);
                $deliveryFee = (float)($info['courier_charge'] ?? $info['delivery_charge'] ?? $info['delivery_fee'] ?? 0);
            }
            if ($deliveryFee <= 0) {
                $deliveryFee = (float)($co->other_charges ?: 120);
            }
            $returnFee = (float)($deliveryFee * 0.5);
            $courierReturnLoss += ($deliveryFee + $returnFee);
        }

        // Net Order Profit = Gross Profit - Courier Delivery Cost - Other Expenses - Courier Return Loss
        $netOrderProfit = $orderGrossProfit - $totalCourierDeliveryCost - $otherExpenses - $courierReturnLoss;
        $totalSalesWithShipping = $totalSellingPrice + $totalShippingRevenue;
        $netMarginPct = $totalSalesWithShipping > 0 
            ? round(($netOrderProfit / $totalSalesWithShipping) * 100, 1) 
            : 0;

        $avgProfitPerOrder = $totalOrders > 0 ? round($netOrderProfit / $totalOrders, 2) : 0;
        $avgProfitPerUnit = $totalUnitsSold > 0 ? round($netOrderProfit / $totalUnitsSold, 2) : 0;

        $isProfit = $netOrderProfit >= 0;
        $netTitle = $isProfit ? 'Net Order Profit' : 'Net Order Loss';
        $netDesc = $isProfit ? 'Final Net Profit after all deductions' : 'Total Net Loss incurred (Costs exceed revenue)';
        $netColor = $isProfit ? '#10b981' : '#ef4444';

        // Waterfall / Breakdown Structure for Step-by-Step UI
        $breakdownItems = [
            [
                'title'       => 'Selling Price',
                'amount'      => round($totalSellingPrice, 2),
                'sign'        => '+',
                'type'        => 'income',
                'color'       => '#13565e',
                'description' => "Total {$totalOrders} " . ($totalOrders === 1 ? 'Order' : 'Orders') . ", {$totalUnitsSold} " . ($totalUnitsSold === 1 ? 'Item' : 'Items') . " Sold",
                'sub_info'    => "{$totalOrders} Orders • {$totalUnitsSold} Qty",
            ],
            [
                'title'       => 'Shipping Charge',
                'amount'      => round($totalShippingRevenue, 2),
                'sign'        => '+',
                'type'        => 'income',
                'color'       => '#078e9a',
                'description' => "Delivery fee from {$totalOrders} " . ($totalOrders === 1 ? 'Order' : 'Orders'),
                'sub_info'    => "{$totalOrders} Orders",
            ],
            [
                'title'       => 'Purchase Cost / COGS',
                'amount'      => round($totalPurchaseCost, 2),
                'sign'        => '-',
                'type'        => 'cost',
                'color'       => '#f59e0b',
                'description' => "Buy cost for {$totalUnitsSold} " . ($totalUnitsSold === 1 ? 'Item' : 'Items') . " Sold",
                'sub_info'    => "{$totalUnitsSold} Qty Purchase Cost",
            ],
            [
                'title'       => 'Courier Delivery Cost',
                'amount'      => round($totalCourierDeliveryCost, 2),
                'sign'        => '-',
                'type'        => 'cost',
                'color'       => '#6366f1',
                'description' => "Paid to Courier for {$courierDeliveryOrdersCount} Shipped/Delivered " . ($courierDeliveryOrdersCount === 1 ? 'Order' : 'Orders'),
                'sub_info'    => "{$courierDeliveryOrdersCount} Dispatched Orders",
            ],
            [
                'title'       => 'Return / Courier Loss',
                'amount'      => round($courierReturnLoss, 2),
                'sign'        => '-',
                'type'        => 'loss',
                'color'       => '#8b5cf6',
                'description' => "Loss from {$returnedOrdersCount} " . ($returnedOrdersCount === 1 ? 'Returned/Cancelled Order' : 'Returned/Cancelled Orders'),
                'sub_info'    => "{$returnedOrdersCount} Returned Orders",
            ],
            [
                'title'       => 'Other Expenses',
                'amount'      => round($otherExpenses, 2),
                'sign'        => '-',
                'type'        => 'expense',
                'color'       => '#ef4444',
                'description' => 'Operating & Administrative Costs',
                'sub_info'    => 'General Indirect Expenses',
            ],
            [
                'title'       => $netTitle,
                'amount'      => round($netOrderProfit, 2),
                'sign'        => $isProfit ? '=' : '-',
                'type'        => 'profit',
                'color'       => $netColor,
                'description' => $netDesc,
                'sub_info'    => $isProfit ? 'Final Net Profit' : 'Final Net Loss',
            ],
        ];

        return [
            'total_orders'                => $totalOrders,
            'total_units_sold'            => $totalUnitsSold,
            'total_selling_price'         => round($totalSellingPrice, 2),
            'total_shipping_revenue'      => round($totalShippingRevenue, 2),
            'total_purchase_cost'         => round($totalPurchaseCost, 2),
            'total_courier_delivery_cost' => round($totalCourierDeliveryCost, 2),
            'order_gross_profit'          => round($orderGrossProfit, 2),
            'other_expenses'              => round($otherExpenses, 2),
            'courier_return_loss'         => round($courierReturnLoss, 2),
            'net_order_profit'            => round($netOrderProfit, 2),
            'net_margin_pct'              => $netMarginPct,
            'avg_profit_per_order'        => $avgProfitPerOrder,
            'avg_profit_per_unit'         => $avgProfitPerUnit,
            'is_profit'                   => $isProfit,
            'breakdown_items'             => $breakdownItems,
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\ChartOfAccount;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Party;
use App\Models\Product;
use App\Models\RiskSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RiskManagementService
{
    /**
     * Get or initialize Risk Settings for a company
     */
    public static function getSettings(int $companyId): RiskSetting
    {
        return RiskSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'block_over_credit_orders'   => true,
                'block_negative_cash'        => true,
                'unusual_expense_threshold'  => 25000.00,
                'block_below_cost_sale'      => true,
                'lock_backdated_entries'     => false,
                'backdated_entry_lock_days'  => 30,
                'high_return_threshold_pct'  => 30,
                'block_negative_stock'       => true,
                'dead_stock_threshold_days'  => 60,
            ]
        );
    }

    /**
     * Update Risk Settings for a company
     */
    public static function updateSettings(int $companyId, array $data): RiskSetting
    {
        $settings = self::getSettings($companyId);
        $settings->update($data);
        return $settings;
    }

    /**
     * Evaluate comprehensive risk profile for a customer
     */
    public static function evaluateCustomerRisk(Party|int $customer, float $newOrderDue = 0): array
    {
        if (is_int($customer)) {
            $customer = Party::withoutGlobalScopes()->find($customer);
        }

        if (!$customer) {
            return [
                'customer_id'       => null,
                'risk_level'        => 'unknown',
                'risk_score'        => 0,
                'allow_credit_sale' => true,
                'risk_reasons'      => [],
            ];
        }

        $currentDue = (float)($customer->due_amount ?? 0);
        $creditLimit = (float)($customer->credit_limit ?? 0);
        $projectedDue = $currentDue + max(0, $newOrderDue);
        
        $creditExceeded = false;
        $exceededAmount = 0;
        $utilizationPct = 0;

        if ($creditLimit > 0) {
            $utilizationPct = round(($projectedDue / $creditLimit) * 100, 1);
            if ($projectedDue > $creditLimit) {
                $creditExceeded = true;
                $exceededAmount = round($projectedDue - $creditLimit, 2);
            }
        }

        // ── Courier & Delivery Risk Evaluation ──
        $orders = Order::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->get(['id', 'status', 'grand_total', 'created_at']);

        $totalOrders = $orders->count();
        $deliveredOrders = $orders->whereIn('status', [Status::Delivered->value, Status::Completed->value, Status::Paid->value])->count();
        $returnedOrders = $orders->whereIn('status', [Status::Returned->value, 'returned', 'cancelled'])->count();
        $cancelledOrders = $orders->whereIn('status', [Status::Cancelled->value, 'cancelled', 'rejected'])->count();

        $deliveredPct = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 100;
        $returnRatePct = $totalOrders > 0 ? round(($returnedOrders / $totalOrders) * 100, 1) : 0;

        $isHighReturnRisk = ($returnedOrders >= 2 && $returnRatePct >= 30) || ($totalOrders >= 2 && $returnRatePct >= 50);

        // ── Risk Score Calculation (0 safe to 100 extreme risk) ──
        $riskScore = 0;
        $riskReasons = [];

        if ($creditExceeded) {
            $riskScore += 45;
            $riskReasons[] = "Credit limit exceeded by ৳" . number_format($exceededAmount, 2) . " (Limit: ৳" . number_format($creditLimit, 2) . ")";
        } elseif ($creditLimit > 0 && $utilizationPct >= 85) {
            $riskScore += 20;
            $riskReasons[] = "Credit limit utilization is critically high ({$utilizationPct}%)";
        }

        if ($isHighReturnRisk) {
            $riskScore += 40;
            $riskReasons[] = "High Courier Return Rate ({$returnRatePct}% returns across {$totalOrders} orders)";
        }

        if ($customer->risk_level === 'blacklisted') {
            $riskScore = 100;
            $riskReasons[] = "Customer is manually blacklisted in system";
        }

        $calculatedRiskLevel = 'low';
        if ($riskScore >= 70 || $customer->risk_level === 'blacklisted') {
            $calculatedRiskLevel = 'critical';
        } elseif ($riskScore >= 40) {
            $calculatedRiskLevel = 'high';
        } elseif ($riskScore >= 20) {
            $calculatedRiskLevel = 'medium';
        }

        $allowCreditSale = !$creditExceeded && ($customer->risk_level !== 'blacklisted');
        $requiresAdvanceShipping = $isHighReturnRisk || ($calculatedRiskLevel === 'high') || ($calculatedRiskLevel === 'critical');

        return [
            'customer_id'               => $customer->id,
            'customer_name'             => $customer->name,
            'customer_phone'            => $customer->phone,
            'credit_limit'              => $creditLimit,
            'credit_days'               => (int)($customer->credit_days ?? 0),
            'current_due'               => $currentDue,
            'projected_due'             => $projectedDue,
            'credit_utilization_pct'    => $utilizationPct,
            'is_credit_exceeded'        => $creditExceeded,
            'credit_exceeded_by'        => $exceededAmount,
            'total_orders'              => $totalOrders,
            'delivered_orders'          => $deliveredOrders,
            'returned_orders'           => $returnedOrders,
            'cancelled_orders'          => $cancelledOrders,
            'delivery_success_rate'     => $deliveredPct,
            'return_rate_pct'           => $returnRatePct,
            'is_high_return_risk'       => $isHighReturnRisk,
            'requires_advance_shipping' => $requiresAdvanceShipping,
            'risk_score'                => min(100, $riskScore),
            'risk_level'                => $calculatedRiskLevel,
            'allow_credit_sale'         => $allowCreditSale,
            'risk_reasons'              => $riskReasons,
        ];
    }

    /**
     * Pre-flight Order Risk Validator (Called before saving Sales Orders / POS orders)
     * Throws ValidationException if any active company policy is violated.
     */
    public static function validateOrderPreFlight(
        int $companyId,
        ?int $partyId,
        array $items,
        float $dueAmount = 0,
        ?string $orderDate = null,
        bool $bypassRisk = false
    ): void {
        if ($bypassRisk) {
            return;
        }

        $settings = self::getSettings($companyId);

        // 1. Backdated Entry Lock Check
        if ($settings->lock_backdated_entries && !empty($orderDate)) {
            $diffDays = Carbon::parse($orderDate)->diffInDays(Carbon::today(), false);
            if ($diffDays > $settings->backdated_entry_lock_days) {
                throw ValidationException::withMessages([
                    'order_date' => ["Risk Policy Violation: Backdated order entry older than {$settings->backdated_entry_lock_days} days is locked by company security policy."]
                ]);
            }
        }

        // 2. Customer Credit Limit Check
        if ($settings->block_over_credit_orders && $partyId && $dueAmount > 0) {
            $customer = Party::withoutGlobalScopes()->find($partyId);
            if ($customer && $customer->credit_limit > 0) {
                $eval = self::evaluateCustomerRisk($customer, $dueAmount);
                if ($eval['is_credit_exceeded']) {
                    throw ValidationException::withMessages([
                        'credit_limit' => ["Credit Risk Breach: Customer '{$customer->name}' credit limit of ৳" . number_format($customer->credit_limit, 2) . " will be exceeded by ৳" . number_format($eval['credit_exceeded_by'], 2) . ". Order blocked."]
                    ]);
                }
            }
        }

        // 3. Item Level Checks (Price Floor & Negative Stock)
        foreach ($items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            if (!$productId) continue;

            $product = Product::withoutGlobalScopes()->find($productId);
            if (!$product) continue;

            $qty = (float)($item['quantity'] ?? 1);
            $unitPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
            $costPrice = (float)($product->purchase_price ?? 0);

            // Negative Stock Guard
            if ($settings->block_negative_stock && $product->manage_stock) {
                if ($product->available_stock < $qty) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => ["Inventory Risk: Product '{$product->title}' has insufficient stock (Available: {$product->available_stock}, Requested: {$qty}). Negative stock sales are blocked."]
                    ]);
                }
            }

            // Price Floor / Below Cost Sale Guard
            if ($settings->block_below_cost_sale && $costPrice > 0) {
                if ($unitPrice < $costPrice) {
                    throw ValidationException::withMessages([
                        "items.{$index}.unit_price" => ["Fraud & Loss Prevention: Product '{$product->title}' selling price (৳{$unitPrice}) is lower than purchase cost (৳{$costPrice}). Below-cost sales are blocked."]
                    ]);
                }
            }
        }
    }

    /**
     * Negative Cash & Liquidity Guard (Called before Expense / Payment out)
     */
    public static function validatePaymentLiquidity(
        int $companyId,
        int $chartOfAccountId,
        float $paymentAmount
    ): void {
        $settings = self::getSettings($companyId);
        if (!$settings->block_negative_cash) {
            return;
        }

        $account = ChartOfAccount::withoutGlobalScopes()->find($chartOfAccountId);
        if (!$account) return;

        $debits = (float)DB::table('transaction_journal_accounts')
            ->join('transaction_journals', 'transaction_journal_accounts.transaction_journal_id', '=', 'transaction_journals.id')
            ->where('transaction_journals.company_id', $companyId)
            ->where('transaction_journal_accounts.chart_of_account_id', $chartOfAccountId)
            ->sum('transaction_journal_accounts.debit');

        $credits = (float)DB::table('transaction_journal_accounts')
            ->join('transaction_journals', 'transaction_journal_accounts.transaction_journal_id', '=', 'transaction_journals.id')
            ->where('transaction_journals.company_id', $companyId)
            ->where('transaction_journal_accounts.chart_of_account_id', $chartOfAccountId)
            ->sum('transaction_journal_accounts.credit');

        $currentBalance = $debits - $credits;
        $projectedBalance = $currentBalance - $paymentAmount;

        // If company has active ledger entries and projected balance goes negative
        if ($debits > 0 && $projectedBalance < 0) {
            throw ValidationException::withMessages([
                'expense_from_id' => ["Liquidity Risk Breach: Account '{$account->name}' has insufficient balance (Current: ৳" . number_format($currentBalance, 2) . ", Payment: ৳" . number_format($paymentAmount, 2) . "). Overdraft/negative cash is blocked by company policy."]
            ]);
        }
    }

    /**
     * Dead Stock & Locked Capital Analytics
     */
    public static function getDeadStockAnalytics(int $companyId): array
    {
        $settings = self::getSettings($companyId);
        $days = $settings->dead_stock_threshold_days ?: 60;
        $cutoffDate = Carbon::now()->subDays($days);

        // Find products with positive available stock
        $products = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('manage_stock', 1)
            ->where('available_stock', '>', 0)
            ->get(['id', 'title', 'sku_code', 'available_stock', 'purchase_price', 'regular_price', 'created_at']);

        // Find product IDs sold since cutoffDate
        $activeSoldProductIds = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.company_id', $companyId)
            ->where('orders.created_at', '>=', $cutoffDate)
            ->whereNotIn('orders.status', [Status::Cancelled->value, 'cancelled', 'rejected'])
            ->distinct()
            ->pluck('order_details.product_id')
            ->toArray();

        $deadStockItems = [];
        $totalDeadCapital = 0;
        $totalDeadUnits = 0;

        foreach ($products as $product) {
            if (!in_array($product->id, $activeSoldProductIds)) {
                $lockedCapital = round($product->available_stock * (float)$product->purchase_price, 2);
                $totalDeadCapital += $lockedCapital;
                $totalDeadUnits += $product->available_stock;

                $deadStockItems[] = [
                    'id'              => $product->id,
                    'title'           => $product->title,
                    'sku_code'        => $product->sku_code,
                    'available_stock' => $product->available_stock,
                    'purchase_price'  => (float)$product->purchase_price,
                    'regular_price'   => (float)$product->regular_price,
                    'locked_capital'  => $lockedCapital,
                    'days_inactive'   => Carbon::parse($product->created_at)->diffInDays(Carbon::now()),
                ];
            }
        }

        usort($deadStockItems, fn($a, $b) => $b['locked_capital'] <=> $a['locked_capital']);

        return [
            'threshold_days'     => $days,
            'total_dead_items'   => count($deadStockItems),
            'total_dead_units'   => $totalDeadUnits,
            'total_dead_capital' => $totalDeadCapital,
            'items'              => array_slice($deadStockItems, 0, 20),
        ];
    }

    /**
     * Cash & Bank Liquidity Risk Monitor
     */
    public static function getLiquidityRiskAnalytics(int $companyId): array
    {
        $accounts = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->with(['accountGroup'])
            ->get();

        $atRiskAccounts = [];
        $totalLiquidity = 0;

        foreach ($accounts as $acc) {
            $groupName = strtolower($acc->accountGroup->name ?? '');
            $groupType = strtolower($acc->accountGroup->account_type ?? '');

            // Only inspect cash, bank, or current assets
            if (!str_contains($groupName, 'cash') && !str_contains($groupName, 'bank') && !str_contains($groupType, 'asset')) {
                continue;
            }

            $debits = (float)DB::table('transaction_journal_accounts')
                ->join('transaction_journals', 'transaction_journal_accounts.transaction_journal_id', '=', 'transaction_journals.id')
                ->where('transaction_journals.company_id', $companyId)
                ->where('transaction_journal_accounts.chart_of_account_id', $acc->id)
                ->sum('transaction_journal_accounts.debit');

            $credits = (float)DB::table('transaction_journal_accounts')
                ->join('transaction_journals', 'transaction_journal_accounts.transaction_journal_id', '=', 'transaction_journals.id')
                ->where('transaction_journals.company_id', $companyId)
                ->where('transaction_journal_accounts.chart_of_account_id', $acc->id)
                ->sum('transaction_journal_accounts.credit');

            $bal = round($debits - $credits, 2);
            $totalLiquidity += $bal;

            if ($bal <= 5000) {
                $atRiskAccounts[] = [
                    'id'           => $acc->id,
                    'account_code' => "COA-{$acc->id}",
                    'account_name' => $acc->name,
                    'account_type' => $acc->accountGroup->name ?? 'Current Asset',
                    'balance'      => $bal,
                    'is_negative'  => $bal < 0,
                    'risk_status'  => $bal < 0 ? 'Negative / Overdraft' : 'Critically Low Balance',
                ];
            }
        }

        return [
            'total_available_liquidity' => $totalLiquidity,
            'at_risk_accounts_count'    => count($atRiskAccounts),
            'accounts'                  => $atRiskAccounts,
        ];
    }

    /**
     * Get Company-wide Enterprise Risk Intelligence Dashboard
     */
    public static function getCompanyRiskOverview(int $companyId): array
    {
        $settings = self::getSettings($companyId);

        // 1. Over-credit limit customers & High return risk
        $customers = Party::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', Party::TYPE_CUSTOMER)
            ->get();

        $overLimitCustomers = [];
        $highReturnRiskCustomers = [];

        foreach ($customers as $c) {
            $eval = self::evaluateCustomerRisk($c);
            if ($eval['is_credit_exceeded']) {
                $overLimitCustomers[] = $eval;
            }
            if ($eval['is_high_return_risk']) {
                $highReturnRiskCustomers[] = $eval;
            }
        }

        // 2. Over-credit limit suppliers
        $suppliers = Party::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', Party::TYPE_SUPPLIER)
            ->where('credit_limit', '>', 0)
            ->whereRaw('due_amount > credit_limit')
            ->get(['id', 'name', 'phone', 'due_amount', 'credit_limit']);

        // 3. Stockout Risk (Products with available_stock <= 5)
        $stockoutRisk = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('manage_stock', 1)
            ->where('available_stock', '<=', 5)
            ->orderBy('available_stock', 'asc')
            ->limit(10)
            ->get(['id', 'title', 'sku_code', 'available_stock', 'purchase_price', 'regular_price']);

        // 4. Dead Stock & Capital Lockup
        $deadStock = self::getDeadStockAnalytics($companyId);

        // 5. Liquidity Risk
        $liquidity = self::getLiquidityRiskAnalytics($companyId);

        return [
            'summary' => [
                'total_customers_count'        => $customers->count(),
                'over_credit_limit_customers'  => count($overLimitCustomers),
                'high_return_risk_customers'   => count($highReturnRiskCustomers),
                'over_limit_suppliers_count'   => $suppliers->count(),
                'stockout_risk_products_count' => $stockoutRisk->count(),
                'total_dead_capital'           => $deadStock['total_dead_capital'],
                'dead_stock_items_count'       => $deadStock['total_dead_items'],
                'at_risk_accounts_count'       => $liquidity['at_risk_accounts_count'],
            ],
            'settings'                     => $settings,
            'over_credit_limit_customers'  => array_slice($overLimitCustomers, 0, 15),
            'high_return_risk_customers'   => array_slice($highReturnRiskCustomers, 0, 15),
            'over_limit_suppliers'         => $suppliers,
            'stockout_risk_products'       => $stockoutRisk,
            'dead_stock'                   => $deadStock,
            'liquidity'                    => $liquidity,
        ];
    }
}

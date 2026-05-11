<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\PurchaseDetail;
use App\Models\Purchase;
use App\Models\TransactionIncome;
use App\Models\TransactionExpense;

class BalanceSheetReportService
{
    private array $deliveredStatuses = [Status::Delivered->value];

    public function generate(string $startDate, string $endDate): array
    {
        // ══════════════════════════════
        //  INCOME
        // ══════════════════════════════

        // 1. Delivered order income
        $orderIncome = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as amount')
            ->first();

        // 2. Transaction incomes (Approved)
        $transactionIncomes = TransactionIncome::whereBetween('date', [$startDate, $endDate])
            ->where('status', Status::Approved->value)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();

        $totalIncome = (float) $orderIncome->amount + (float) $transactionIncomes->amount;

        // ══════════════════════════════
        //  EXPENSE
        // ══════════════════════════════

        // 1. Purchase cost (date range এ যা purchase হয়েছে)
        $purchaseCost = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', Status::Completed->value)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as amount')
            ->first();

        // 2. Transaction expenses (Approved)
        $transactionExpenses = TransactionExpense::whereBetween('date', [$startDate, $endDate])
            ->where('status', Status::Approved->value)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();

        $totalExpense = (float) $purchaseCost->amount + (float) $transactionExpenses->amount;

        // ══════════════════════════════
        //  NET
        // ══════════════════════════════
        $netBalance  = $totalIncome - $totalExpense;

        return [
            'date_range' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
            'income' => [
                'order_income' => [
                    'label'  => 'Delivered Order Income',
                    'count'  => (int) $orderIncome->count,
                    'amount' => round((float) $orderIncome->amount, 2),
                ],
                'transaction_income' => [
                    'label'  => 'Other Income',
                    'count'  => (int) $transactionIncomes->count,
                    'amount' => round((float) $transactionIncomes->amount, 2),
                ],
                'total_income' => round($totalIncome, 2),
            ],
            'expense' => [
                'purchase_cost' => [
                    'label'  => 'Purchase Cost',
                    'count'  => (int) $purchaseCost->count,
                    'amount' => round((float) $purchaseCost->amount, 2),
                ],
                'transaction_expense' => [
                    'label'  => 'Other Expenses',
                    'count'  => (int) $transactionExpenses->count,
                    'amount' => round((float) $transactionExpenses->amount, 2),
                ],
                'total_expense' => round($totalExpense, 2),
            ],
            'net_balance' => round($netBalance, 2),
            'is_profit'   => $netBalance >= 0,
        ];
    }
}

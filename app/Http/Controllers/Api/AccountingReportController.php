<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\TransactionJournal;
use App\Models\TransactionJournalAccount;
use App\Services\AutoAccountingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingReportController extends Controller
{
    private function getFyContext(int $companyId): array
    {
        $setting = AutoAccountingService::getSetting($companyId);
        $start = $setting->financial_year_start ? \Carbon\Carbon::parse($setting->financial_year_start)->format('Y-m-d') : Carbon::now()->startOfYear()->format('Y-m-d');
        $end = $setting->financial_year_end ? \Carbon\Carbon::parse($setting->financial_year_end)->format('Y-m-d') : Carbon::now()->endOfYear()->format('Y-m-d');
        return [
            'financial_year_start'     => $start,
            'financial_year_end'       => $end,
            'financial_year_title'     => $setting->financial_year_title ?? 'Current FY',
            'is_financial_year_locked' => (bool)$setting->is_financial_year_locked,
        ];
    }

    /**
     * 1. Tally Day Book (All Vouchers for Date/Range)
     */
    public function dayBook(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $fy = $this->getFyContext($companyId);
        $fromDate = $request->get('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::today()->format('Y-m-d'));
        $voucherType = $request->get('voucher_type'); // journal, payment, receipt, contra, sales, purchase

        $query = TransactionJournal::with(['accounts.chartOfAccount.accountGroup', 'party:id,name,phone', 'creator:id,name'])
            ->where('company_id', $companyId)
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);

        if ($voucherType && $voucherType !== 'all') {
            $query->where('voucher_type', $voucherType);
        }

        if ($request->filled('search')) {
            $s = $request->get('search');
            $query->where(function ($q) use ($s) {
                $q->where('voucher_no', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%")
                  ->orWhere('narration', 'like', "%{$s}%")
                  ->orWhereHas('party', fn($pq) => $pq->where('name', 'like', "%{$s}%"));
            });
        }

        $vouchers = $query->orderBy('date', 'desc')->latest('id')->paginate($request->get('per_page', 50));

        $totalDebit = (clone $query)->sum('total_debit');
        $totalCredit = (clone $query)->sum('total_credit');

        return ResponseHelper::success([
            'financial_year' => $fy,
            'vouchers'       => $vouchers,
            'summary'        => [
                'total_debit'  => (float)$totalDebit,
                'total_credit' => (float)$totalCredit,
                'from_date'    => $fromDate,
                'to_date'      => $toDate,
            ],
        ], 'Day book retrieved');
    }

    /**
     * 2. General Ledger (Account Statement with Running Balance)
     */
    public function generalLedger(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $accountId = $request->get('chart_of_account_id');
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::now()->format('Y-m-d'));

        if (!$accountId) {
            // Return default cash or first active account
            $firstAccount = ChartOfAccount::where('company_id', $companyId)->first();
            $accountId = $firstAccount ? $firstAccount->id : null;
        }

        if (!$accountId) {
            return ResponseHelper::success(['items' => [], 'summary' => []], 'No accounts found');
        }

        $account = ChartOfAccount::with('accountGroup')->find($accountId);
        $accountType = $account?->accountGroup?->account_type ?? 'Assets';
        $isDebitNature = in_array($accountType, ['Assets', 'Expense']);

        // 1. Calculate Opening Balance before fromDate
        $priorJournals = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $fromDate) {
            $q->where('company_id', $companyId)->whereDate('date', '<', $fromDate);
        })->where('chart_of_account_id', $accountId)->get();

        $priorDebit = $priorJournals->sum('debit');
        $priorCredit = $priorJournals->sum('credit');
        $openingBalance = $isDebitNature ? ($priorDebit - $priorCredit) : ($priorCredit - $priorDebit);

        // 2. Fetch Period Journal Entries
        $entries = TransactionJournalAccount::with(['transactionJournal.party', 'transactionJournal.creator'])
            ->whereHas('transactionJournal', function ($q) use ($companyId, $fromDate, $toDate) {
                $q->where('company_id', $companyId)
                  ->whereDate('date', '>=', $fromDate)
                  ->whereDate('date', '<=', $toDate);
            })
            ->where('chart_of_account_id', $accountId)
            ->get()
            ->sortBy(fn($item) => $item->transactionJournal->date . '-' . $item->id);

        $running = $openingBalance;
        $formattedEntries = [];
        $totalPeriodDebit = 0;
        $totalPeriodCredit = 0;

        foreach ($entries as $row) {
            $dr = (float)$row->debit;
            $cr = (float)$row->credit;
            $totalPeriodDebit += $dr;
            $totalPeriodCredit += $cr;

            if ($isDebitNature) {
                $running += ($dr - $cr);
            } else {
                $running += ($cr - $dr);
            }

            $journal = $row->transactionJournal;
            $formattedEntries[] = [
                'id'             => $row->id,
                'journal_id'     => $journal->id,
                'date'           => $journal->date,
                'voucher_type'   => $journal->voucher_type ?? 'Journal',
                'voucher_no'     => $journal->voucher_no ?? $journal->reference_number,
                'party_name'     => $journal->party?->name ?? null,
                'narration'      => $journal->narration ?? $journal->description,
                'debit'          => $dr,
                'credit'         => $cr,
                'running_balance'=> round($running, 2),
                'balance_type'   => $running >= 0 ? ($isDebitNature ? 'Dr' : 'Cr') : ($isDebitNature ? 'Cr' : 'Dr'),
            ];
        }

        return ResponseHelper::success([
            'financial_year'  => $this->getFyContext($companyId),
            'account'         => $account,
            'opening_balance' => round($openingBalance, 2),
            'opening_type'    => $openingBalance >= 0 ? ($isDebitNature ? 'Dr' : 'Cr') : ($isDebitNature ? 'Cr' : 'Dr'),
            'entries'         => array_values($formattedEntries),
            'closing_balance' => round($running, 2),
            'closing_type'    => $running >= 0 ? ($isDebitNature ? 'Dr' : 'Cr') : ($isDebitNature ? 'Cr' : 'Dr'),
            'summary'         => [
                'total_debit'  => round($totalPeriodDebit, 2),
                'total_credit' => round($totalPeriodCredit, 2),
                'net_change'   => round($totalPeriodDebit - $totalPeriodCredit, 2),
            ]
        ], 'General ledger retrieved');
    }

    /**
     * 3. Tally Trial Balance (রেওয়ামিল)
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $fy = $this->getFyContext($companyId);
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));

        $groups = AccountGroup::with(['chartOfAccount'])
            ->where('company_id', $companyId)
            ->get();

        $groupRows = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;

        foreach ($groups as $group) {
            $accountRows = [];
            $groupDebit = 0;
            $groupCredit = 0;
            $isDebitNature = in_array($group->account_type, ['Assets', 'Expense']);

            foreach ($group->chartOfAccount as $account) {
                $journals = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate);
                })->where('chart_of_account_id', $account->id)->get();

                $dr = (float)$journals->sum('debit');
                $cr = (float)$journals->sum('credit');
                $net = $dr - $cr;

                if ($dr == 0 && $cr == 0) continue;

                $finalDebit = $net > 0 ? $net : 0;
                $finalCredit = $net < 0 ? abs($net) : 0;

                $groupDebit += $finalDebit;
                $groupCredit += $finalCredit;

                $accountRows[] = [
                    'id'            => $account->id,
                    'name'          => $account->name,
                    'total_debit'   => $dr,
                    'total_credit'  => $cr,
                    'net_debit'     => round($finalDebit, 2),
                    'net_credit'    => round($finalCredit, 2),
                ];
            }

            if (!empty($accountRows)) {
                $grandTotalDebit += $groupDebit;
                $grandTotalCredit += $groupCredit;

                $groupRows[] = [
                    'id'           => $group->id,
                    'name'         => $group->name,
                    'account_type' => $group->account_type,
                    'group_debit'  => round($groupDebit, 2),
                    'group_credit' => round($groupCredit, 2),
                    'accounts'     => $accountRows,
                ];
            }
        }

        return ResponseHelper::success([
            'financial_year'     => $fy,
            'as_of_date'         => $asOfDate,
            'groups'             => $groupRows,
            'grand_total_debit'  => round($grandTotalDebit, 2),
            'grand_total_credit' => round($grandTotalCredit, 2),
            'is_balanced'        => round($grandTotalDebit, 2) === round($grandTotalCredit, 2),
            'difference'         => round(abs($grandTotalDebit - $grandTotalCredit), 2),
        ], 'Trial balance retrieved');
    }

    /**
     * 4. Tally Profit & Loss Statement (লাভ-ক্ষতি বিবরণী)
     */
    public function profitAndLoss(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $fy = $this->getFyContext($companyId);
        $fromDate = $request->get('from_date', $fy['financial_year_start']);
        $toDate = $request->get('to_date', Carbon::now()->format('Y-m-d'));

        // 1. Direct Sales & Revenues
        $incomeAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Income');
        })->where('company_id', $companyId)->get();

        $incomeRows = [];
        $totalRevenue = 0;
        foreach ($incomeAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $fromDate, $toDate) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $fromDate)->whereDate('date', '<=', $toDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('credit') - $j->sum('debit'));
            if ($amount != 0) {
                $incomeRows[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($amount, 2)];
                $totalRevenue += $amount;
            }
        }

        // 2. Cost of Goods Sold & Direct Expenses
        $cogsAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('name', 'like', '%Direct%')->orWhere('name', 'like', '%COGS%');
        })->where('company_id', $companyId)->get();

        $cogsRows = [];
        $totalCogs = 0;
        foreach ($cogsAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $fromDate, $toDate) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $fromDate)->whereDate('date', '<=', $toDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('debit') - $j->sum('credit'));
            if ($amount != 0) {
                $cogsRows[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($amount, 2)];
                $totalCogs += $amount;
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;

        // 3. Operating & Indirect Expenses
        $expenseAccounts = ChartOfAccount::whereHas('accountGroup', function ($q) {
            $q->where('account_type', 'Expense')->where('name', 'not like', '%Direct%');
        })->where('company_id', $companyId)->get();

        $expenseRows = [];
        $totalOperatingExpense = 0;
        foreach ($expenseAccounts as $acc) {
            $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $fromDate, $toDate) {
                $q->where('company_id', $companyId)->whereDate('date', '>=', $fromDate)->whereDate('date', '<=', $toDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $amount = (float)($j->sum('debit') - $j->sum('credit'));
            if ($amount != 0) {
                $expenseRows[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($amount, 2)];
                $totalOperatingExpense += $amount;
            }
        }

        $netProfit = $grossProfit - $totalOperatingExpense;

        return ResponseHelper::success([
            'financial_year'          => $fy,
            'from_date'               => $fromDate,
            'to_date'                 => $toDate,
            'sales_revenue'           => ['items' => $incomeRows, 'total' => round($totalRevenue, 2)],
            'cost_of_goods_sold'      => ['items' => $cogsRows, 'total' => round($totalCogs, 2)],
            'gross_profit'            => round($grossProfit, 2),
            'operating_expenses'      => ['items' => $expenseRows, 'total' => round($totalOperatingExpense, 2)],
            'net_profit'              => round($netProfit, 2),
            'is_profit'               => $netProfit >= 0,
        ], 'Profit and loss statement retrieved');
    }

    /**
     * 5. Tally Balance Sheet (আর্থিক অবস্থার বিবরণী)
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $fy = $this->getFyContext($companyId);
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));

        // 1. Assets
        $assetGroups = AccountGroup::with('chartOfAccount')
            ->where('company_id', $companyId)
            ->where('account_type', 'Assets')
            ->get();

        $assetRows = [];
        $totalAssets = 0;
        foreach ($assetGroups as $grp) {
            $items = [];
            $grpTotal = 0;
            foreach ($grp->chartOfAccount as $acc) {
                $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate);
                })->where('chart_of_account_id', $acc->id)->get();

                $bal = (float)($j->sum('debit') - $j->sum('credit'));
                if ($bal != 0) {
                    $items[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($bal, 2)];
                    $grpTotal += $bal;
                }
            }
            if (!empty($items)) {
                $assetRows[] = ['group_name' => $grp->name, 'items' => $items, 'total' => round($grpTotal, 2)];
                $totalAssets += $grpTotal;
            }
        }

        // 2. Liabilities
        $liabilityGroups = AccountGroup::with('chartOfAccount')
            ->where('company_id', $companyId)
            ->where('account_type', 'Liability')
            ->get();

        $liabilityRows = [];
        $totalLiabilities = 0;
        foreach ($liabilityGroups as $grp) {
            $items = [];
            $grpTotal = 0;
            foreach ($grp->chartOfAccount as $acc) {
                $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate);
                })->where('chart_of_account_id', $acc->id)->get();

                $bal = (float)($j->sum('credit') - $j->sum('debit'));
                if ($bal != 0) {
                    $items[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($bal, 2)];
                    $grpTotal += $bal;
                }
            }
            if (!empty($items)) {
                $liabilityRows[] = ['group_name' => $grp->name, 'items' => $items, 'total' => round($grpTotal, 2)];
                $totalLiabilities += $grpTotal;
            }
        }

        // 3. Equity & Net Profit
        $equityGroups = AccountGroup::with('chartOfAccount')
            ->where('company_id', $companyId)
            ->where('account_type', 'Equity')
            ->get();

        $equityRows = [];
        $totalEquity = 0;
        foreach ($equityGroups as $grp) {
            $items = [];
            $grpTotal = 0;
            foreach ($grp->chartOfAccount as $acc) {
                $j = TransactionJournalAccount::whereHas('transactionJournal', function ($q) use ($companyId, $asOfDate) {
                    $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate);
                })->where('chart_of_account_id', $acc->id)->get();

                $bal = (float)($j->sum('credit') - $j->sum('debit'));
                if ($bal != 0) {
                    $items[] = ['id' => $acc->id, 'name' => $acc->name, 'amount' => round($bal, 2)];
                    $grpTotal += $bal;
                }
            }
            if (!empty($items)) {
                $equityRows[] = ['group_name' => $grp->name, 'items' => $items, 'total' => round($grpTotal, 2)];
                $totalEquity += $grpTotal;
            }
        }

        // Net Profit Calculation for Retained Earnings
        $allIncomes = TransactionJournalAccount::whereHas('chartOfAccount.accountGroup', fn($q) => $q->where('account_type', 'Income'))
            ->whereHas('transactionJournal', fn($q) => $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate))
            ->get();
        $totalInc = $allIncomes->sum('credit') - $allIncomes->sum('debit');

        $allExpenses = TransactionJournalAccount::whereHas('chartOfAccount.accountGroup', fn($q) => $q->where('account_type', 'Expense'))
            ->whereHas('transactionJournal', fn($q) => $q->where('company_id', $companyId)->whereDate('date', '<=', $asOfDate))
            ->get();
        $totalExp = $allExpenses->sum('debit') - $allExpenses->sum('credit');

        $retainedProfit = $totalInc - $totalExp;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity + $retainedProfit;

        return ResponseHelper::success([
            'financial_year'               => $fy,
            'as_of_date'                   => $asOfDate,
            'assets'                       => ['groups' => $assetRows, 'total' => round($totalAssets, 2)],
            'liabilities'                  => ['groups' => $liabilityRows, 'total' => round($totalLiabilities, 2)],
            'equity'                       => ['groups' => $equityRows, 'total' => round($totalEquity, 2)],
            'current_period_profit'        => round($retainedProfit, 2),
            'total_liabilities_and_equity' => round($totalLiabilitiesAndEquity, 2),
            'is_balanced'                  => round($totalAssets, 2) === round($totalLiabilitiesAndEquity, 2),
            'difference'                   => round(abs($totalAssets - $totalLiabilitiesAndEquity), 2),
        ], 'Balance sheet retrieved');
    }
}

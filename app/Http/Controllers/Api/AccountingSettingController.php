<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AccountingSetting;
use App\Services\AutoAccountingService;
use App\Services\DefaultAccountingSeederService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $setting = AutoAccountingService::getSetting($companyId);

        return ResponseHelper::success($setting->load([
            'cashAccount',
            'bankAccount',
            'receivableAccount',
            'payableAccount',
            'posSalesAccount',
            'webSalesAccount',
            'projectRevenueAccount',
            'deliveryIncomeAccount',
            'courierExpenseAccount',
            'cogsAccount',
            'inventoryAccount',
            'wipAccount',
            'finishedGoodsAccount',
            'salaryExpenseAccount',
            'productionLossAccount',
            'vatPayableAccount',
        ]), 'Accounting settings retrieved');
    }

    public function update(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $setting = AutoAccountingService::getSetting($companyId);

        $validated = $request->validate([
            'accounting_mode'                   => 'required|in:simple,advanced',
            'auto_journal_posting'              => 'required|boolean',
            'sync_website_orders'               => 'nullable|boolean',
            'sync_pos_orders'                   => 'nullable|boolean',
            'sync_landing_page_orders'          => 'nullable|boolean',
            'sync_sales_returns'                => 'nullable|boolean',
            'sync_purchases'                    => 'nullable|boolean',
            'sync_purchase_returns'             => 'nullable|boolean',
            'sync_supplier_payments'            => 'nullable|boolean',
            'sync_customer_receipts'            => 'nullable|boolean',
            'sync_income_entries'               => 'nullable|boolean',
            'sync_expense_entries'              => 'nullable|boolean',
            'sync_project_revenue'              => 'nullable|boolean',
            'sync_payroll'                      => 'nullable|boolean',
            'sync_manufacturing'                => 'nullable|boolean',
            'financial_year_start'              => 'nullable|date',
            'financial_year_end'                => 'nullable|date|after_or_equal:financial_year_start',
            'financial_year_title'              => 'nullable|string|max:50',
            'is_financial_year_locked'          => 'nullable|boolean',
            'default_cash_account_id'           => 'nullable|exists:chart_of_accounts,id',
            'default_bank_account_id'           => 'nullable|exists:chart_of_accounts,id',
            'default_receivable_account_id'     => 'nullable|exists:chart_of_accounts,id',
            'default_payable_account_id'        => 'nullable|exists:chart_of_accounts,id',
            'default_pos_sales_account_id'      => 'nullable|exists:chart_of_accounts,id',
            'default_web_sales_account_id'      => 'nullable|exists:chart_of_accounts,id',
            'default_sales_return_account_id'   => 'nullable|exists:chart_of_accounts,id',
            'default_project_revenue_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_delivery_income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_courier_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_cogs_account_id'           => 'nullable|exists:chart_of_accounts,id',
            'default_purchase_return_account_id'=> 'nullable|exists:chart_of_accounts,id',
            'default_inventory_account_id'      => 'nullable|exists:chart_of_accounts,id',
            'default_wip_account_id'            => 'nullable|exists:chart_of_accounts,id',
            'default_finished_goods_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_salary_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'default_production_loss_account_id'=> 'nullable|exists:chart_of_accounts,id',
            'default_vat_payable_account_id'    => 'nullable|exists:chart_of_accounts,id',
        ]);

        $setting->update($validated);

        return ResponseHelper::success($setting, 'Accounting settings updated successfully');
    }

    public function seedDefaults(): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $setting = DefaultAccountingSeederService::seedDefaultAccountsForCompany($companyId);

        return ResponseHelper::success($setting, 'Default chart of accounts initialized successfully');
    }

    public function closeFinancialYear(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? 1;
        $setting = AutoAccountingService::getSetting($companyId);

        // Lock old FY and advance to next FY
        if ($setting->financial_year_start && $setting->financial_year_end) {
            $oldEnd = \Carbon\Carbon::parse($setting->financial_year_end);
            $newStart = $oldEnd->copy()->addDay()->format('Y-m-d');
            $newEnd = $oldEnd->copy()->addYear()->format('Y-m-d');
            
            $startYear = \Carbon\Carbon::parse($newStart)->year;
            $endYear = \Carbon\Carbon::parse($newEnd)->year;
            $newTitle = "FY {$startYear}-{$endYear}";

            $setting->update([
                'financial_year_start'     => $newStart,
                'financial_year_end'       => $newEnd,
                'financial_year_title'     => $newTitle,
                'is_financial_year_locked' => false,
            ]);
        }

        return ResponseHelper::success($setting, 'Financial year closed and new financial year initialized successfully');
    }
}

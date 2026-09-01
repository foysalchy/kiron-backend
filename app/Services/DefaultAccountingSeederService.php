<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\AccountGroup;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefaultAccountingSeederService
{
    /**
     * Ensure default Chart of Accounts and AccountingSettings exist for a company
     */
    public static function seedDefaultAccountsForCompany(int $companyId): AccountingSetting
    {
        return DB::transaction(function () use ($companyId) {
            // 1. Ensure or create AccountingSetting
            $setting = AccountingSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->first();

            $now = Carbon::now();
            $currYear = $now->year;
            if ($now->month >= 7) {
                $fyStart = "{$currYear}-07-01";
                $fyEnd = ($currYear + 1) . "-06-30";
                $fyTitle = "FY {$currYear}-" . ($currYear + 1);
            } else {
                $fyStart = ($currYear - 1) . "-07-01";
                $fyEnd = "{$currYear}-06-30";
                $fyTitle = "FY " . ($currYear - 1) . "-{$currYear}";
            }

            if (!$setting) {
                $setting = AccountingSetting::withoutGlobalScopes()->create([
                    'company_id'               => $companyId,
                    'accounting_mode'          => 'advanced',
                    'auto_journal_posting'     => true,
                    'financial_year_start'     => $fyStart,
                    'financial_year_end'       => $fyEnd,
                    'financial_year_title'     => $fyTitle,
                    'is_financial_year_locked' => false,
                ]);
            } else if (!$setting->financial_year_start) {
                $setting->update([
                    'financial_year_start'     => $fyStart,
                    'financial_year_end'       => $fyEnd,
                    'financial_year_title'     => $fyTitle,
                    'is_financial_year_locked' => false,
                ]);
            }

            // 2. Default Groups Definition
            $groupsDef = [
                'Current Assets' => ['type' => 'Assets', 'desc' => 'Cash, Bank, Receivables & Inventory'],
                'Fixed Assets' => ['type' => 'Assets', 'desc' => 'Long term physical assets'],
                'Current Liabilities' => ['type' => 'Liability', 'desc' => 'Payables, Duties & Taxes'],
                'Long Term Liabilities' => ['type' => 'Liability', 'desc' => 'Loans & Mortgages'],
                'Capital Account' => ['type' => 'Equity', 'desc' => 'Owner Capital & Reserves'],
                'Sales Accounts' => ['type' => 'Income', 'desc' => 'Direct Sales and Revenue'],
                'Indirect Incomes' => ['type' => 'Income', 'desc' => 'Delivery, interest & other incomes'],
                'Direct Expenses' => ['type' => 'Expense', 'desc' => 'COGS, raw materials & production'],
                'Indirect Expenses' => ['type' => 'Expense', 'desc' => 'Admin, salaries, rent & utilities'],
            ];

            $groups = [];
            foreach ($groupsDef as $name => $info) {
                $group = AccountGroup::withoutGlobalScopes()->firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $name,
                    ],
                    [
                        'account_type' => $info['type'],
                        'description' => $info['desc'],
                        'status' => Status::Active->value,
                    ]
                );
                $groups[$name] = $group->id;
            }

            // 3. Default Chart of Accounts Definition
            $coaDef = [
                'cash'             => ['group' => 'Current Assets', 'name' => 'Cash in Hand', 'desc' => 'Petty cash and drawer cash'],
                'bank'             => ['group' => 'Current Assets', 'name' => 'Main Bank Account', 'desc' => 'Primary corporate bank account'],
                'receivable'       => ['group' => 'Current Assets', 'name' => 'Sundry Debtors (Accounts Receivable)', 'desc' => 'Customer outstanding balances'],
                'inventory'        => ['group' => 'Current Assets', 'name' => 'Stock-in-Hand / Inventory', 'desc' => 'Warehouse raw & finished materials'],
                'wip'              => ['group' => 'Current Assets', 'name' => 'Work-in-Progress (WIP) Inventory', 'desc' => 'Goods under manufacturing process'],
                'finished_goods'   => ['group' => 'Current Assets', 'name' => 'Finished Goods Inventory', 'desc' => 'Manufactured products ready for sale'],
                'payable'          => ['group' => 'Current Liabilities', 'name' => 'Sundry Creditors (Accounts Payable)', 'desc' => 'Supplier pending invoices'],
                'vat'              => ['group' => 'Current Liabilities', 'name' => 'Duties & Taxes (VAT Payable)', 'desc' => 'Collected VAT and sales taxes'],
                'capital'          => ['group' => 'Capital Account', 'name' => 'Owner Capital Account', 'desc' => 'Owner equity and investments'],
                'retained'         => ['group' => 'Capital Account', 'name' => 'Retained Earnings / P&L Surplus', 'desc' => 'Accumulated profits'],
                'pos_sales'        => ['group' => 'Sales Accounts', 'name' => 'POS Sales Revenue A/c', 'desc' => 'Point of sale counter orders'],
                'web_sales'        => ['group' => 'Sales Accounts', 'name' => 'Online / Website Sales Revenue A/c', 'desc' => 'eCommerce & website orders'],
                'project_revenue'  => ['group' => 'Sales Accounts', 'name' => 'Project Revenue A/c', 'desc' => 'Project milestone and contract billing'],
                'delivery_income'  => ['group' => 'Indirect Incomes', 'name' => 'Delivery & Shipping Charge Income', 'desc' => 'Delivery fees collected from buyers'],
                'cogs'             => ['group' => 'Direct Expenses', 'name' => 'Cost of Goods Sold (COGS)', 'desc' => 'Direct product acquisition & manufacturing cost'],
                'project_cost'     => ['group' => 'Direct Expenses', 'name' => 'Project Direct Costs', 'desc' => 'Labor and material used in projects'],
                'production_loss'  => ['group' => 'Direct Expenses', 'name' => 'Production Wastage & Scrap Loss', 'desc' => 'Manufacturing material loss & damage'],
                'salary_expense'   => ['group' => 'Indirect Expenses', 'name' => 'Salaries & Wages Expense A/c', 'desc' => 'Employee payroll and compensation'],
                'rent_expense'     => ['group' => 'Indirect Expenses', 'name' => 'Office Rent Expense A/c', 'desc' => 'Premises and warehouse rent'],
                'courier_expense'  => ['group' => 'Indirect Expenses', 'name' => 'Courier & Freight Expense A/c', 'desc' => 'Parcel and delivery charges paid'],
                'discount_expense' => ['group' => 'Indirect Expenses', 'name' => 'Discount Allowed A/c', 'desc' => 'Sales discount and promotional cuts'],
                'sales_return'     => ['group' => 'Sales Accounts', 'name' => 'Sales Returns & Allowances A/c', 'desc' => 'Returned products from customers'],
                'purchase_return'  => ['group' => 'Direct Expenses', 'name' => 'Purchase Returns & Outward A/c', 'desc' => 'Goods returned back to suppliers'],
                'purchase_account' => ['group' => 'Direct Expenses', 'name' => 'Purchase Account', 'desc' => 'Direct supplier goods purchases'],
            ];

            $coaMap = [];
            foreach ($coaDef as $key => $item) {
                $groupId = $groups[$item['group']] ?? null;
                if (!$groupId) continue;

                $account = ChartOfAccount::withoutGlobalScopes()->firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $item['name'],
                    ],
                    [
                        'account_group_id' => $groupId,
                        'description' => $item['desc'],
                        'status' => Status::Active->value,
                    ]
                );
                $coaMap[$key] = $account->id;
            }

            // 4. Update Mapping in AccountingSetting
            $setting->update([
                'default_cash_account_id' => $setting->default_cash_account_id ?? ($coaMap['cash'] ?? null),
                'default_bank_account_id' => $setting->default_bank_account_id ?? ($coaMap['bank'] ?? null),
                'default_receivable_account_id' => $setting->default_receivable_account_id ?? ($coaMap['receivable'] ?? null),
                'default_payable_account_id' => $setting->default_payable_account_id ?? ($coaMap['payable'] ?? null),
                'default_pos_sales_account_id' => $setting->default_pos_sales_account_id ?? ($coaMap['pos_sales'] ?? null),
                'default_web_sales_account_id' => $setting->default_web_sales_account_id ?? ($coaMap['web_sales'] ?? null),
                'default_sales_return_account_id' => $setting->default_sales_return_account_id ?? ($coaMap['sales_return'] ?? null),
                'default_project_revenue_account_id' => $setting->default_project_revenue_account_id ?? ($coaMap['project_revenue'] ?? null),
                'default_delivery_income_account_id' => $setting->default_delivery_income_account_id ?? ($coaMap['delivery_income'] ?? null),
                'default_courier_expense_account_id' => $setting->default_courier_expense_account_id ?? ($coaMap['courier_expense'] ?? null),
                'default_cogs_account_id' => $setting->default_cogs_account_id ?? ($coaMap['cogs'] ?? null),
                'default_purchase_return_account_id' => $setting->default_purchase_return_account_id ?? ($coaMap['purchase_return'] ?? null),
                'default_inventory_account_id' => $setting->default_inventory_account_id ?? ($coaMap['inventory'] ?? null),
                'default_wip_account_id' => $setting->default_wip_account_id ?? ($coaMap['wip'] ?? null),
                'default_finished_goods_account_id' => $setting->default_finished_goods_account_id ?? ($coaMap['finished_goods'] ?? null),
                'default_salary_expense_account_id' => $setting->default_salary_expense_account_id ?? ($coaMap['salary_expense'] ?? null),
                'default_production_loss_account_id' => $setting->default_production_loss_account_id ?? ($coaMap['production_loss'] ?? null),
                'default_vat_payable_account_id' => $setting->default_vat_payable_account_id ?? ($coaMap['vat'] ?? null),
            ]);

            return $setting;
        });
    }
}

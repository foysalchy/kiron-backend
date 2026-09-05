<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingSetting extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'auto_journal_posting'     => 'boolean',
        'sync_website_orders'      => 'boolean',
        'sync_pos_orders'          => 'boolean',
        'sync_landing_page_orders' => 'boolean',
        'sync_sales_returns'       => 'boolean',
        'sync_purchases'           => 'boolean',
        'sync_purchase_returns'    => 'boolean',
        'sync_supplier_payments'   => 'boolean',
        'sync_customer_receipts'   => 'boolean',
        'sync_income_entries'      => 'boolean',
        'sync_expense_entries'     => 'boolean',
        'sync_project_revenue'     => 'boolean',
        'sync_payroll'             => 'boolean',
        'sync_manufacturing'       => 'boolean',
        'is_financial_year_locked' => 'boolean',
        'financial_year_start'     => 'date:Y-m-d',
        'financial_year_end'       => 'date:Y-m-d',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_cash_account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_bank_account_id');
    }

    public function receivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_receivable_account_id');
    }

    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_payable_account_id');
    }

    public function posSalesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_pos_sales_account_id');
    }

    public function webSalesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_web_sales_account_id');
    }

    public function salesReturnAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_sales_return_account_id');
    }

    public function projectRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_project_revenue_account_id');
    }

    public function deliveryIncomeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_delivery_income_account_id');
    }

    public function courierExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_courier_expense_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_cogs_account_id');
    }

    public function purchaseReturnAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_purchase_return_account_id');
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_inventory_account_id');
    }

    public function wipAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_wip_account_id');
    }

    public function finishedGoodsAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_finished_goods_account_id');
    }

    public function salaryExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_salary_expense_account_id');
    }

    public function productionLossAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_production_loss_account_id');
    }

    public function vatPayableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_vat_payable_account_id');
    }
}

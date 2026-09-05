<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            // Sales & Orders Toggles
            $table->boolean('sync_website_orders')->default(true)->after('auto_journal_posting');
            $table->boolean('sync_pos_orders')->default(true)->after('sync_website_orders');
            $table->boolean('sync_landing_page_orders')->default(true)->after('sync_pos_orders');
            $table->boolean('sync_sales_returns')->default(true)->after('sync_landing_page_orders');

            // Purchases & Suppliers Toggles
            $table->boolean('sync_purchases')->default(true)->after('sync_sales_returns');
            $table->boolean('sync_purchase_returns')->default(true)->after('sync_purchases');
            $table->boolean('sync_supplier_payments')->default(true)->after('sync_purchase_returns');

            // Receipts & Incomes Toggles
            $table->boolean('sync_customer_receipts')->default(true)->after('sync_supplier_payments');
            $table->boolean('sync_income_entries')->default(true)->after('sync_customer_receipts');

            // Expenses, Projects & HR Toggles
            $table->boolean('sync_expense_entries')->default(true)->after('sync_income_entries');
            $table->boolean('sync_project_revenue')->default(true)->after('sync_expense_entries');
            $table->boolean('sync_payroll')->default(true)->after('sync_project_revenue');
            $table->boolean('sync_manufacturing')->default(true)->after('sync_payroll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sync_website_orders',
                'sync_pos_orders',
                'sync_landing_page_orders',
                'sync_sales_returns',
                'sync_purchases',
                'sync_purchase_returns',
                'sync_supplier_payments',
                'sync_customer_receipts',
                'sync_income_entries',
                'sync_expense_entries',
                'sync_project_revenue',
                'sync_payroll',
                'sync_manufacturing',
            ]);
        });
    }
};

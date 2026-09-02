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
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('accounting_mode', 20)->default('simple'); // simple, advanced
            $table->boolean('auto_journal_posting')->default(true);

            // Default Chart of Account Mappings
            $table->foreignId('default_cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_bank_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_receivable_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_payable_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_pos_sales_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_web_sales_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_project_revenue_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_delivery_income_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_cogs_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_inventory_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_wip_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_finished_goods_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_salary_expense_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_production_loss_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('default_vat_payable_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};

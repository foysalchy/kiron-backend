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
            if (!Schema::hasColumn('accounting_settings', 'default_courier_expense_account_id')) {
                $table->foreignId('default_courier_expense_account_id')
                    ->nullable()
                    ->after('default_delivery_income_account_id')
                    ->constrained('chart_of_accounts')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_settings', 'default_courier_expense_account_id')) {
                $table->dropForeign(['default_courier_expense_account_id']);
                $table->dropColumn('default_courier_expense_account_id');
            }
        });
    }
};

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
            $table->foreignId('default_sales_return_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete()->after('default_web_sales_account_id');
            $table->foreignId('default_purchase_return_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete()->after('default_cogs_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->dropForeign(['default_sales_return_account_id']);
            $table->dropForeign(['default_purchase_return_account_id']);
            $table->dropColumn([
                'default_sales_return_account_id',
                'default_purchase_return_account_id',
            ]);
        });
    }
};

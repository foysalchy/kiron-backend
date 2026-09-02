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
        if (!Schema::hasTable('risk_settings')) {
            Schema::create('risk_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->unique()->index();
                
                // 1. Financial & Credit Guard
                $table->boolean('block_over_credit_orders')->default(true);
                $table->boolean('block_negative_cash')->default(true);
                $table->decimal('unusual_expense_threshold', 12, 2)->default(25000.00);

                // 2. Fraud & Loss Prevention Guard
                $table->boolean('block_below_cost_sale')->default(true);
                $table->boolean('lock_backdated_entries')->default(false);
                $table->integer('backdated_entry_lock_days')->default(30);
                $table->integer('high_return_threshold_pct')->default(30);

                // 3. Inventory & Supply Chain Guard
                $table->boolean('block_negative_stock')->default(true);
                $table->integer('dead_stock_threshold_days')->default(60);

                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_settings');
    }
};

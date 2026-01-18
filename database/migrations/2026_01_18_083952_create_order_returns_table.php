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
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('parties')->onDelete('set null');
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');

            // Return Info
            $table->string('return_no')->unique();
            $table->date('return_date');
            $table->text('reason')->nullable();

            // Totals
            $table->integer('total_quantities')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0)->comment('Manual entry');
            $table->decimal('discount_on_all', 10, 2)->default(0)->comment('Manual entry');
            $table->decimal('coupon_discount', 10, 2)->default(0)->comment('Manual entry');
            $table->decimal('round_off', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Refund tracking
            $table->decimal('refund_amount', 15, 2)->default(0)->comment('Total refunded');

            // Status
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=cleared, 2=not_cleared, 3=waiting, 4=cancelled');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};

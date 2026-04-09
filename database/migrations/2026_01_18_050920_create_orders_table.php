<?php

use App\Enums\Status;
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
        Schema::create('orders', function (Blueprint $table) {
           $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('parties')->onDelete('set null');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');

            // Order Type & Info
            $table->string('type')->comment('pos, sales');
            $table->string('order_no')->unique();
            $table->string('reference_no')->nullable();
            $table->json('shipping_address')->nullable();
            $table->date('order_date');
            $table->boolean('is_walk_in')->default(false);

            // Totals
            $table->integer('total_quantities')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('discount_on_all', 10, 2)->default(0);
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('round_off', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Payment Status (amount stored in order_payments table)
            $table->decimal('payment_amount', 15, 2)->default(0)->comment('Total paid amount');
            $table->tinyInteger('payment_status')->default(0)->comment('0=unpaid, 1=partial, 2=paid');

            // Status
            $table->tinyInteger('status')->default(Status::Pending->value);
            $table->text('note')->nullable();
            $table->text('hold_ref')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

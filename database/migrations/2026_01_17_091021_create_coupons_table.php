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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');

            // Coupon Info
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Discount Type & Value
            $table->tinyInteger('discount_type')->comment('1=fixed, 2=percentage');
            $table->decimal('discount_value', 15, 2);
            $table->decimal('max_discount_amount', 15, 2)->nullable()->comment('For percentage type');

            // Usage Restrictions
            $table->decimal('min_purchase_amount', 15, 2)->default(0);
            $table->integer('usage_limit')->nullable()->comment('Total usage limit');
            $table->integer('usage_limit_per_customer')->nullable()->default(1);
            $table->integer('used_count')->default(0);

            // Validity
            $table->dateTime('start_date');
            $table->dateTime('end_date');

            // Status
            $table->tinyInteger('status')->default(1)->comment('0=inactive, 1=active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

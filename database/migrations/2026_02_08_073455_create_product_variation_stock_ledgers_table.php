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
        Schema::create('product_variation_stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('cells')->onDelete('set null');

            $table->string('batch_number')->nullable();
            $table->json('serial_numbers')->nullable();
            $table->string('transaction_type');
            $table->string('reference_type')->nullable(); // e.g., 'PurchaseOrder', 'SaleOrder', 'Manual'
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_change'); // Can be negative
            $table->integer('quantity_after')->default(0);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_stock_ledgers');
    }
};

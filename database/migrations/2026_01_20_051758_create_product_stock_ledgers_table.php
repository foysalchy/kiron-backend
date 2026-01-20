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
        Schema::create('product_stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('bins')->onDelete('set null');

            $table->string('batch_number')->nullable();
            $table->json('serial_numbers')->nullable();

            $table->string('transaction_type');

            $table->string('reference_type')->nullable(); // StockMovement, Sale, Purchase
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->integer('quantity_before');
            $table->integer('quantity_change'); // + or -
            $table->integer('quantity_after');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_ledgers');
    }
};

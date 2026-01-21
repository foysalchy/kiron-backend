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
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            // Batch & Bin tracking
            $table->string('batch_number')->nullable();
            $table->foreignId('source_bin_id')->nullable()->constrained('bins')->onDelete('restrict');
            $table->foreignId('destination_bin_id')->nullable()->constrained('bins')->onDelete('restrict');

            $table->integer('quantity');
            $table->json('serial_numbers')->nullable(); // For serialized items
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};

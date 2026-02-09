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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('discount', 15, 2)->nullable()->default(0);
            $table->decimal('tax', 15, 2)->nullable()->default(0);
            $table->decimal('total', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};

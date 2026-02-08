<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds variation support WITHOUT changing existing products table
     */
    public function up(): void
    {
        // Product Variations Table
        // Only created for products where type='variation'
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->decimal('regular_price', 10, 2);
            $table->enum('discount_type', ['flat', 'percent'])->default('flat');
            $table->decimal('discount', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('available_stock')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock'])->default('out_of_stock');
            $table->string('combination_hash')->unique(); // For quick lookup
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'stock_status']);
            $table->index('combination_hash');
        });

        // Product Variation Attributes
        // Links variations to their attribute values
        Schema::create('product_variation_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('attribute_group_id')->constrained('attribute_groups')->onDelete('cascade');
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->onDelete('cascade');
            $table->timestamps();
        });

        // Product Variation Stocks
        // Warehouse-level stock for each variation
        Schema::create('product_variation_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('bin_id')->nullable()->constrained('cells')->onDelete('set null');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_stocks');
        Schema::dropIfExists('product_variation_attributes');
        Schema::dropIfExists('product_variations');
    }
};

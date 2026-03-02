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
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->foreignId('variation_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movement_items', 'variation_id')) {
                $table->dropForeign(['variation_id']);
                $table->dropColumn('variation_id');
            }
        });
    }
};

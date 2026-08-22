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
        Schema::table('wocommerce_settings', function (Blueprint $table) {
            $table->dropColumn('sync');
            $table->boolean('product_sync')->default(0)->after('consumer_secret');
            $table->boolean('order_sync')->default(0)->after('product_sync');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wocommerce_settings', function (Blueprint $table) {
            $table->boolean('sync')->default(0)->after('consumer_secret');
            $table->dropColumn(['product_sync', 'order_sync']);
        });
    }
};

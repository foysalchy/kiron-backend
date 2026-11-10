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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('checkout_show_email')->default(true)->after('login_required_for_checkout');
            $table->boolean('checkout_show_city')->default(true)->after('checkout_show_email');
            $table->boolean('product_details_quick_order')->default(true)->after('checkout_show_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_show_email',
                'checkout_show_city',
                'product_details_quick_order',
            ]);
        });
    }
};

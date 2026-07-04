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
        Schema::table('content_settings', function (Blueprint $table) {
            $table->string('page_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_settings', function (Blueprint $table) {
            $table->enum('page_type', [
                'product_page',
                'checkout_page',
                'all_page',
                'cart_page',
                'product_page_sub',
                'footer_bottom_right',
            ])->change();
        });
    }
};

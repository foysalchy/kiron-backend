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
        Schema::table('sms_sends', function (Blueprint $table) {
            $table->json('supplier_ids')->nullable()->default(null)->change();
            $table->json('customer_ids')->nullable()->default(null)->change();
            $table->json('custom_numbers')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_sends', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_ids',
                'customer_ids',
                'custom_numbers',
            ]);
        });
    }
};

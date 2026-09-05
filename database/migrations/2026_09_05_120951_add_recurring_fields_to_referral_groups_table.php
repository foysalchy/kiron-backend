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
        Schema::table('referral_groups', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(true)->after('is_tiered');
            $table->json('recurring_rates')->nullable()->after('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_groups', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurring_rates']);
        });
    }
};

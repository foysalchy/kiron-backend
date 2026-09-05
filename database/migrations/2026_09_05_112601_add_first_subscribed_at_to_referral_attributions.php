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
        Schema::table('referral_attributions', function (Blueprint $table) {
            $table->timestamp('first_subscribed_at')->nullable()->after('status');
            $table->decimal('total_commission_earned', 12, 2)->default(0.00)->after('first_subscribed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_attributions', function (Blueprint $table) {
            $table->dropColumn(['first_subscribed_at', 'total_commission_earned']);
        });
    }
};

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
        Schema::table('markets', function (Blueprint $table) {
            if (!Schema::hasColumn('markets', 'instant_purchase_event')) {
                $table->boolean('instant_purchase_event')->default(true);
            }
            if (!Schema::hasColumn('markets', 'purchase_event_status')) {
                $table->string('purchase_event_status')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'fb_purchase_event_fired')) {
                $table->boolean('fb_purchase_event_fired')->default(false);
            }
            if (!Schema::hasColumn('orders', 'fb_cancel_event_fired')) {
                $table->boolean('fb_cancel_event_fired')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['instant_purchase_event', 'purchase_event_status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fb_purchase_event_fired', 'fb_cancel_event_fired']);
        });
    }
};

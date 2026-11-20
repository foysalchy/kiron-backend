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
        if (Schema::hasTable('ip_settings')) {
            Schema::table('ip_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('ip_settings', 'sms_limit_minutes')) {
                    $table->integer('sms_limit_minutes')->default(5)->nullable()->after('order_limit');
                }
                if (!Schema::hasColumn('ip_settings', 'sms_limit_count')) {
                    $table->integer('sms_limit_count')->default(3)->nullable()->after('sms_limit_minutes');
                }
                if (!Schema::hasColumn('ip_settings', 'order_limit_minutes')) {
                    $table->integer('order_limit_minutes')->default(5)->nullable()->after('sms_limit_count');
                }
                if (!Schema::hasColumn('ip_settings', 'order_limit_count')) {
                    $table->integer('order_limit_count')->default(1)->nullable()->after('order_limit_minutes');
                }
                if (!Schema::hasColumn('ip_settings', 'honeypot_filter_enabled')) {
                    $table->boolean('honeypot_filter_enabled')->default(true)->after('order_limit_count');
                }
                if (!Schema::hasColumn('ip_settings', 'block_checkout_only')) {
                    $table->boolean('block_checkout_only')->default(true)->after('honeypot_filter_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ip_settings')) {
            Schema::table('ip_settings', function (Blueprint $table) {
                $table->dropColumn([
                    'sms_limit_minutes',
                    'sms_limit_count',
                    'order_limit_minutes',
                    'order_limit_count',
                    'honeypot_filter_enabled',
                    'block_checkout_only',
                ]);
            });
        }
    }
};

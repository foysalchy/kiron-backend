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
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'checkout_sms_verify')) {
                    $table->boolean('checkout_sms_verify')->default(false)->after('sms_forget_password');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'is_otp_verified')) {
                    $table->boolean('is_otp_verified')->nullable()->default(null)->after('status');
                }
                if (!Schema::hasColumn('orders', 'otp_code')) {
                    $table->string('otp_code', 10)->nullable()->after('is_otp_verified');
                }
                if (!Schema::hasColumn('orders', 'otp_expires_at')) {
                    $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'checkout_sms_verify')) {
                    $table->dropColumn('checkout_sms_verify');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = ['is_otp_verified', 'otp_code', 'otp_expires_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

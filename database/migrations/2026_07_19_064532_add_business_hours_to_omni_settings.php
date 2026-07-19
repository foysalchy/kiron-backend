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
        Schema::table('omni_settings', function (Blueprint $table) {
            Schema::table('omni_settings', function (Blueprint $table) {
                $table->time('business_hours_start')->default('10:00:00')->after('welcome_message');
                $table->time('business_hours_end')->default('22:00:00')->after('business_hours_start');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('omni_settings', function (Blueprint $table) {
            $table->dropColumn('business_hours_start');
            $table->dropColumn('business_hours_end');
        });
    }
};

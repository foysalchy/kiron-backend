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
            $table->decimal('inside_charge', 10, 2)->default(60.00)->after('phone');
            $table->decimal('outside_charge', 10, 2)->default(100.00)->after('inside_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
             $table->dropColumn(['inside_charge', 'outside_charge']);
        });
    }
};

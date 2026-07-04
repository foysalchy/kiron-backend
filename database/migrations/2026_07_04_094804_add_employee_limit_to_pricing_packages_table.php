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
        Schema::table('pricing_packages', function (Blueprint $table) {
            $table->integer('employee_limit')->nullable()->after('user_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_packages', function (Blueprint $table) {
            $table->dropColumn('employee_limit');
        });
    }
};

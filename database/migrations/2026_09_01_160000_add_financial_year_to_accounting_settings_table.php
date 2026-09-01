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
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->date('financial_year_start')->nullable()->after('auto_journal_posting');
            $table->date('financial_year_end')->nullable()->after('financial_year_start');
            $table->string('financial_year_title', 50)->nullable()->after('financial_year_end'); // e.g. "FY 2025-2026"
            $table->boolean('is_financial_year_locked')->default(false)->after('financial_year_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->dropColumn([
                'financial_year_start',
                'financial_year_end',
                'financial_year_title',
                'is_financial_year_locked',
            ]);
        });
    }
};

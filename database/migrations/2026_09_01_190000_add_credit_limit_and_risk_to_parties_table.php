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
        Schema::table('parties', function (Blueprint $table) {
            if (!Schema::hasColumn('parties', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('due_amount')->comment('Max allowed due balance. 0 = No limit');
            }
            if (!Schema::hasColumn('parties', 'credit_days')) {
                $table->integer('credit_days')->default(0)->after('credit_limit')->comment('Max allowed due days. 0 = No limit');
            }
            if (!Schema::hasColumn('parties', 'risk_level')) {
                $table->string('risk_level', 20)->default('low')->after('credit_days')->comment('low, medium, high, blacklisted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'credit_days', 'risk_level']);
        });
    }
};

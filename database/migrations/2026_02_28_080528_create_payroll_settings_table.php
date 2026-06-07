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
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->integer('late_days_for_penalty')->default(3); // 3 days late
            $table->decimal('penalty_amount_in_days', 8, 2)->default(1.0); // = 1 day salary deduct
            $table->boolean('has_overtime_allowance')->default(false);
            $table->decimal('overtime_rate_multiplier', 8, 2)->default(1.0); // 1.5x of basic hourly rate
            $table->integer('standard_working_hours')->default(8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};

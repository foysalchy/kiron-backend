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
        Schema::create('generate_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('pay_roll_id')->constrained('pay_rolls')->cascadeOnDelete();
            $table->foreignId('pay_slip_id')->constrained('pay_slip_managers')->cascadeOnDelete();
            $table->foreignId('pay_roll_pay_head_id')->nullable()->constrained('pay_roll_pay_heads')->cascadeOnDelete();
            $table->date('generated_date')->comment('e.g., January 2026');
            $table->decimal('gross_salary', 15, 2)->default(0.00);  
            $table->decimal('total_deduction', 15, 2)->default(0.00); 
            $table->decimal('net_salary', 15, 2)->default(0.00);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generate_payslips');
    }
};

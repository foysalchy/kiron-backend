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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('employee_type_id')->constrained('employee_types')->cascadeOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->cascadeOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained('office_locations')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('phone');
            $table->string('email')->unique()->nullable();
            $table->string('gender');
            $table->date('dob');
            $table->string('image')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('payslip_generation_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->boolean('is_same_address')->default(false);
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->boolean('allow_flexible_time')->default(false);
            $table->tinyInteger('status')->default(1)->comment('0: Inactive, 1: Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

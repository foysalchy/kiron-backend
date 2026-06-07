<?php

use App\Enums\Status;
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
        Schema::create('resignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('letter')->nullable()->comment('img or pdf');
            $table->string('type')->comment('resignation, termination');
            $table->json('resign_rule_ids');
            $table->date('letter_received_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('activities')->nullable()->comment('good or bad');
            $table->boolean('is_applied')->default(false);
            $table->tinyInteger('status')->default(Status::Pending->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resignations');
    }
};

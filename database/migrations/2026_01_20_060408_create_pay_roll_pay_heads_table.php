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
        Schema::create('pay_roll_pay_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('pay_roll_id')->constrained('pay_rolls')->cascadeOnDelete();
            $table->foreignId('pay_head_id')->constrained('pay_heads')->cascadeOnDelete();
            $table->string('type')->comment('amount,percentage');
            $table->decimal('amount', 15, 2)->default(0);
            $table->unique(['pay_roll_id', 'pay_head_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_roll_pay_heads');
    }
};

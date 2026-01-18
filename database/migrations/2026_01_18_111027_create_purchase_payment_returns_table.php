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
        Schema::create('purchase_payment_returns', function (Blueprint $table) {
            $table->id();
             $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');

            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->comment('cash, card, bank, mobile_banking, cheque');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_returns');
    }
};

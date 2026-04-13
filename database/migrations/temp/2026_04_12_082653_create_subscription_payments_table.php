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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // payment info
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);

            // transaction info (optional based on method)
            $table->string('transaction_id')->nullable();
            $table->string('sender_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();

            // status
            $table->string('status')->default('pending');
            // pending | success | failed | rejected

            // extra flexible data
            $table->json('meta')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};

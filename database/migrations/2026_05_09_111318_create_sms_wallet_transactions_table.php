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
        Schema::create('sms_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('sms_wallet_id')->constrained('sms_wallets')->cascadeOnDelete();
            $table->string('type')->comment('recharge, deduct');
            $table->integer('sms_count');                 
            $table->decimal('rate_per_sms', 8, 4);
            $table->morphs('reference');                     
            $table->integer('balance_before');           
            $table->integer('balance_after');             
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_wallet_transactions');
    }
};

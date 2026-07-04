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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reminder_setting_id')->constrained()->cascadeOnDelete();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['company_subscription_id', 'reminder_setting_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};

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
        Schema::create('channel_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // facebook, instagram, whatsapp
            $table->string('business_name')->nullable();
            $table->string('page_id')->nullable();
            $table->string('page_name')->nullable();
            $table->string('ig_id')->nullable();
            $table->string('ig_username')->nullable();
            $table->string('waba_id')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('access_token'); // encrypted
            $table->timestamp('connected_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_connections');
    }
};

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
        Schema::create('omni_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('auto_assign')->default(true);
            $table->json('auto_assign_agents')->nullable(); 
            $table->boolean('away_mode_active')->default(true);
            $table->text('away_message')->nullable();
            $table->boolean('welcome_mode_active')->default(true);
            $table->text('welcome_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omni_settings');
    }
};

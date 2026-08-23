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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('default_provider')->default('openai'); // openai or gemini
            
            // OpenAI Settings
            $table->boolean('openai_status')->default(false);
            $table->text('openai_key')->nullable();
            $table->string('openai_model')->default('gpt-4o-mini');
            $table->text('openai_instructions')->nullable();
            
            // Gemini Settings
            $table->boolean('gemini_status')->default(false);
            $table->text('gemini_key')->nullable();
            $table->string('gemini_model')->default('gemini-1.5-flash');
            $table->text('gemini_instructions')->nullable();
            
            $table->timestamps();
            
            // Unique per company
            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};

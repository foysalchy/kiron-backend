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
        Schema::create('status_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->integer('kiron_status'); // The Kiron Status Enum value
            $table->json('mappings')->nullable(); // JSON object for woo, pathao, etc.
            $table->timestamps();
            
            // A company should only have one mapping row per status
            $table->unique(['company_id', 'kiron_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_mappings');
    }
};

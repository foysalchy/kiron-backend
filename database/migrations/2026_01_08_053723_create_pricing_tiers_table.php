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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                ->constrained('pricing_packages')
                ->cascadeOnDelete();

            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly']);

            $table->decimal('regular_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};

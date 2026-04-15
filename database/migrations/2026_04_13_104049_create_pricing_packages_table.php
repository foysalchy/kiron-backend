<?php

use App\Enums\Status;
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
        Schema::create('pricing_packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('mode', ['regular', 'popular', 'recommended'])->default('regular');
            $table->integer('trial_days')->nullable();

            // Limits
            $table->integer('order_limit')->nullable();
            $table->integer('product_limit')->nullable();
            $table->json('invoice_limit')->nullable(); // multi select যেমন [1,2,3]
            $table->integer('user_limit')->nullable();

            // Domain
            $table->string('primary_domain')->nullable(); // 🔥 string as requested
            $table->integer('domain_limit')->nullable();

            // Features (checkbox)
            $table->json('features')->nullable();
            $table->json('multiple_input')->nullable();
            $table->tinyInteger('status')->default(Status::Active->value);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_packages');
    }
};

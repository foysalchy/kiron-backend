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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('alternative_phone', 20)->nullable();
            $table->json('invoice_template')->nullable();
            $table->json('theme_template')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('pricing_package_id')->nullable()->constrained('pricing_packages')->nullOnDelete();
            $table->tinyInteger('business_type')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->boolean('manage_warehouse')->default(false);
            $table->unsignedBigInteger('default_warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

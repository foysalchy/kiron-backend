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
        Schema::create('omni_ad_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('ad_id')->index(); // Facebook Ad ID
            $table->string('title')->nullable(); // Reference title/Campaign name
            $table->unsignedBigInteger('product_id')->nullable()->index(); // Linked product if any
            $table->text('ad_info')->nullable(); // Price, offer details, instructions for this ad
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omni_ad_mappings');
    }
};

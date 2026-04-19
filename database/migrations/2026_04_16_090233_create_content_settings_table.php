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
        Schema::create('content_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('page_type', ['product_page', 'checkout_page', 'all_page', 'cart_page', 'product_page_sub']);
            $table->string('icon_url')->nullable();
            $table->string('icon_file')->nullable();
            $table->string('title')->nullable();       // all_page only
            $table->string('subtitle')->nullable();    // all_page only
            $table->text('text_content')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(Status::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_settings');
    }
};

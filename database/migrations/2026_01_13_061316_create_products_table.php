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
       Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            
            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail');
            $table->string('video_link')->nullable();
            
            // Categories (JSON)
            $table->json('mega_category_ids')->nullable();
            $table->json('sub_category_ids')->nullable();
            $table->json('mini_category_ids')->nullable();
            $table->json('extra_category_ids')->nullable();
            
            // Description
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            
            // Product Type
            $table->enum('type', ['single', 'variation'])->default('single');
            $table->string('sku_code')->nullable();
            
            // Stock
            $table->enum('stock_status', ['in_stock', 'out_of_stock'])->default('in_stock');
            $table->integer('stock_quantity')->default(0);
            $table->integer('available_stock')->default(0);
            
            // Pricing
            $table->decimal('regular_price', 15, 2)->nullable();
            $table->enum('discount_type', ['flat', 'percent'])->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('purpose');
            $table->text('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
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
        Schema::dropIfExists('products');
    }
};

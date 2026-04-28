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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('domain')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->longText('description')->nullable();
            $table->text('pricing')->nullable();
            $table->string('pixel')->nullable();
            $table->text('meta_access_token')->nullable();
            $table->text('header_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('instruction')->nullable();
            $table->json('extras')->nullable();
            $table->string('instruction_title')->nullable();
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
        Schema::dropIfExists('landing_pages');
    }
};

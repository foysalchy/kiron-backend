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
            $table->foreignId('template_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->longText('description')->nullable();
            $table->text('pricing')->nullable();
            $table->string('slug')->unique();
            $table->string('pixel')->nullable();
            $table->text('meta_access_token')->nullable();
            $table->text('header_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('instruction')->nullable();
            $table->string('instruction_title')->nullable();
            $table->tinyInteger('status')->default(Status::Inactive->value);
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

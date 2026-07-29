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
        if (!Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('channel_group_id')->constrained('channel_groups')->cascadeOnDelete();
                $table->string('name'); // ফেসবুক পেজের নাম
                $table->string('type'); // 'facebook'
                $table->string('slug'); // ইউনিক স্ল্যাগ (e.g. fb-page-id)
                $table->string('page_id')->unique();
                $table->string('profile_image')->nullable();
                $table->text('page_token'); // পেজ টোকেন (Encrypted)
                $table->string('color')->default('#1877F2');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};

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
        Schema::table('omni_settings', function (Blueprint $table) {
            $table->boolean('ai_auto_response')->default(false)->after('welcome_message');
            $table->text('ai_custom_instructions')->nullable()->after('ai_auto_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('omni_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_auto_response', 'ai_custom_instructions']);
        });
    }
};

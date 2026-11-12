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
            $table->string('ai_reply_mode')->default('always')->after('ai_auto_response'); // 'always' or 'offline_only'
            $table->boolean('ai_whatsapp_active')->default(true)->after('ai_reply_mode');
            $table->longText('ai_training_data')->nullable()->after('ai_custom_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('omni_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_reply_mode', 'ai_whatsapp_active', 'ai_training_data']);
        });
    }
};

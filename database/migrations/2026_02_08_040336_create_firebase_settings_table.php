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
        Schema::create('firebase_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('email');
            $table->string('api_key');
            $table->string('auth_domain');
            $table->string('project_id');
            $table->string('storage_bucket');
            $table->string('messaging_sender_id');
            $table->string('app_id');
            $table->string('measurement_id')->nullable();
            $table->tinyInteger('google_auth')->default(0);
            $table->tinyInteger('facebook_auth')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_settings');
    }
};

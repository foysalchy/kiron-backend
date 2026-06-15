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
        Schema::create('super_admin_sms_sends', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->json('company_ids')->nullable();
            $table->json('custom_numbers')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admin_sms_sends');
    }
};

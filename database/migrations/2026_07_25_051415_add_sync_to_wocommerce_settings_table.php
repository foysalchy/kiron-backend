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
        Schema::table('wocommerce_settings', function (Blueprint $table) {
            $table->boolean('sync')->default(true)->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('wocommerce_settings', function (Blueprint $table) {
            $table->dropColumn(['sync']);
        });
    }
};

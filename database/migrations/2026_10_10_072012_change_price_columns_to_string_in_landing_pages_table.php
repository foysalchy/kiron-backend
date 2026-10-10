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
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('regular_price')->nullable()->change();
            $table->string('discount_price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->decimal('regular_price', 10, 2)->nullable()->change();
            $table->decimal('discount_price', 10, 2)->nullable()->change();
        });
    }
};

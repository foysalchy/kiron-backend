<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_brands', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable();
            $table->longText('description')->nullable();
        });

        Schema::table('master_features', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable();
        });

        Schema::table('master_demos', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_brands', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description']);
        });

        Schema::table('master_features', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('master_demos', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};

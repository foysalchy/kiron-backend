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
            $table->dropUnique('landing_pages_slug_unique');
            $table->unique(['company_id', 'slug']);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropUnique('blogs_slug_unique');
            $table->unique(['company_id', 'slug']);
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->dropUnique('notices_slug_unique');
            $table->unique(['company_id', 'slug']);
        });

        Schema::table('courier_methods', function (Blueprint $table) {
            $table->dropUnique('courier_methods_slug_unique');
            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courier_methods', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
        });
    }
};

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
        Schema::table('pages', function (Blueprint $table) {
            $table->string('slug')->after('title');
            $table->longText('description')->nullable()->change();
            $table->integer('sort_order')->default(0)->after('status');
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['slug', 'sort_order', 'meta_title', 'meta_description']);
        });
    }
};

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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('meta_image')->nullable()->after('tags');
            $table->string('founder_name')->nullable()->after('meta_image');
            $table->string('founder_designation')->nullable()->after('founder_name');
            $table->date('established')->nullable()->after('founder_designation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['meta_image', 'founder_name', 'founder_designation', 'established']);
        });
    }
};

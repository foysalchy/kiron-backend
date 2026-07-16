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
        Schema::table('parties', function (Blueprint $table) {
            $table->string('fb_psid')->nullable()->after('profile')->index();
            $table->string('ig_id')->nullable()->after('fb_psid')->index();
            $table->string('ig_username')->nullable()->after('ig_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['fb_psid', 'ig_id', 'ig_username']);
        });
    }
};

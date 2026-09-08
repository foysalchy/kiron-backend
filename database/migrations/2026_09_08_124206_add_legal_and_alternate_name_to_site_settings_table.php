<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('shop_name');
            $table->string('alternate_name')->nullable()->after('legal_name');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['legal_name', 'alternate_name']);
        });
    }
};

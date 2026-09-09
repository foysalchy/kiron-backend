<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('shop_name');
            }
            if (!Schema::hasColumn('site_settings', 'alternate_name')) {
                $table->string('alternate_name')->nullable()->after('legal_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'legal_name')) {
                $table->dropColumn('legal_name');
            }
            if (Schema::hasColumn('site_settings', 'alternate_name')) {
                $table->dropColumn('alternate_name');
            }
        });
    }
};

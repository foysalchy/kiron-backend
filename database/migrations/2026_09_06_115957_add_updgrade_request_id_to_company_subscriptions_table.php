<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->foreignId('upgrade_request_id')
                ->nullable()
                ->after('company_id')
                ->constrained('updgrade_package_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['upgrade_request_id']);
            $table->dropColumn('upgrade_request_id');
        });
    }
};
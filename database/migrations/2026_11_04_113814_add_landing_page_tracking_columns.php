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
            if (!Schema::hasColumn('landing_pages', 'views')) {
                $table->unsignedBigInteger('views')->default(0)->after('status');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'landing_page_id')) {
                $table->foreignId('landing_page_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('landing_pages')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'landing_page_id')) {
                $table->dropForeign(['landing_page_id']);
                $table->dropColumn('landing_page_id');
            }
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            if (Schema::hasColumn('landing_pages', 'views')) {
                $table->dropColumn('views');
            }
        });
    }
};

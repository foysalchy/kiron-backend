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
        Schema::table('markets', function (Blueprint $table) {
            if (!Schema::hasColumn('markets', 'fire_cancel_event')) {
                $table->boolean('fire_cancel_event')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            if (Schema::hasColumn('markets', 'fire_cancel_event')) {
                $table->dropColumn('fire_cancel_event');
            }
        });
    }
};

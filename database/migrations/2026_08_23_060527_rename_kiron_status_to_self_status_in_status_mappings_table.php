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
        Schema::table('status_mappings', function (Blueprint $table) {
            $table->renameColumn('kiron_status', 'self_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_mappings', function (Blueprint $table) {
            $table->renameColumn('self_status', 'kiron_status');
        });
    }
};

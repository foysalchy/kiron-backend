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
            // First add an index on company_id to satisfy the foreign key constraint
            $table->index('company_id', 'status_mappings_company_id_index');
            // Then drop unique constraint that prevented multiple rows with the same self_status
            $table->dropUnique('status_mappings_company_id_kiron_status_unique');
            $table->index(['company_id', 'self_status'], 'status_mappings_company_id_self_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_mappings', function (Blueprint $table) {
            $table->dropIndex('status_mappings_company_id_self_status_index');
            $table->unique(['company_id', 'self_status'], 'status_mappings_company_id_kiron_status_unique');
            $table->dropIndex('status_mappings_company_id_index');
        });
    }
};

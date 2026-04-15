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
        Schema::table('domain_setups', function (Blueprint $table) {
             $table->string('product_card_template')->nullable()->after('template_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_setups', function (Blueprint $table) {
             $table->dropColumn('product_card_template');
        });
    }
};

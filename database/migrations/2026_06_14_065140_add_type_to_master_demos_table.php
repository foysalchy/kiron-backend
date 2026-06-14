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
        Schema::table('master_demos', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('title')->comment('1=Landing, 2=E-commerce');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_demos', function (Blueprint $table) {
             $table->dropColumn('type');
        });
    }
};

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
        Schema::table('quik_replies', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('company_id');
            $table->string('type')->default('text')->after('group_name'); // 'text' or 'image'
            $table->text('text')->nullable()->change();
            $table->string('image_path')->nullable()->after('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quik_replies', function (Blueprint $table) {
            $table->dropColumn(['group_name', 'type', 'image_path']);
        });
    }
};

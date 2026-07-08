<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->integer('tax_group_id')->nullable()->change();
        });
        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->integer('tax_group_id')->nullable()->change();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('tax_group_id')->nullable()->change();
        });

        Schema::table('order_return_details', function (Blueprint $table) {
            $table->integer('tax_group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->decimal('tax_group_id', 15, 2)->nullable()->change();
        });
        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->decimal('tax_group_id', 15, 2)->nullable()->change();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('tax_group_id', 15, 2)->nullable()->change();
        });

        Schema::table('order_return_details', function (Blueprint $table) {
            $table->decimal('tax_group_id', 15, 2)->nullable()->change();
        });
    }
};
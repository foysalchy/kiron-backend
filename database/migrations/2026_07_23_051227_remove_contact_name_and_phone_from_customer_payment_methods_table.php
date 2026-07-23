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
        Schema::table('customer_payment_methods', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_payment_methods', function (Blueprint $table) {
            $table->string('contact_name')->after('icon');
            $table->string('phone')->after('contact_name');
        });
    }
};

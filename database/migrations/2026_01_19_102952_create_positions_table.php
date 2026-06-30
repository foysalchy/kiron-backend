<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('pay_roll_id')->constrained('pay_rolls')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->comment('shared,single');
            $table->integer('head_count')->default(0);
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0: Inactive, 1: Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
      public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};

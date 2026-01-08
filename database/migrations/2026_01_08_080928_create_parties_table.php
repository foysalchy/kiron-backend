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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->tinyInteger('type')->comment('1=Supplier, 2=Customer');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('alternative_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('profile')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};

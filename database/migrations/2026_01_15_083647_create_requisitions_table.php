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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('requisition_number')->unique();
            $table->date('request_date');
            $table->date('need_date');
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=approved, 2=rejected, 3=completed');
            $table->text('note')->nullable();
            $table->text('reject_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};

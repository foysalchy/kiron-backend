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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('restrict');
            $table->foreignId('supplier_id')->constrained('parties')->onDelete('restrict');

            // Return Info
            $table->string('return_no')->unique();
            $table->date('return_date');

            // Totals (auto-calculated from items)
            $table->integer('total_quantities')->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Status
            $table->tinyInteger('status')->default(0)->comment('0=draft, 1=completed, 2=cancelled');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};

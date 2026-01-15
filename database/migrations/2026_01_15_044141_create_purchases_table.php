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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('supplier_id')->constrained('parties')->onDelete('restrict');

            // Purchase Info
            $table->string('reference_no')->unique();
            $table->date('purchase_date');

            // Totals
            $table->integer('total_quantities')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('discount_on_all', 10, 2)->default(0);
            $table->decimal('round_off', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Payment Info
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->string('payment_type')->nullable(); // cash, bank, card, cheque
            $table->string('account')->nullable();
            $table->text('payment_note')->nullable();

            // Status
            $table->tinyInteger('status')->default(0)->comment('0=draft, 1=completed, 2=cancelled');
            $table->tinyInteger('payment_status')->default(0)->comment('0=unpaid, 1=partial, 2=paid');

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
        Schema::dropIfExists('purchases');
    }
};

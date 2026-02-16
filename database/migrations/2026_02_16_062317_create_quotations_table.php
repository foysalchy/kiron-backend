<?php

use App\Enums\Status;
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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('quotation_no')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('reference_no')->nullable();

            //customer info
            $table->string('name');
            $table->string('phone');
            $table->string('address');


            // Amounts
            $table->integer('total_items')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_charges', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);


            $table->tinyInteger('status')->default(Status::Draft->value);

            // Additional Info
            $table->text('terms_conditions')->nullable();
            $table->text('note')->nullable();
            $table->text('internal_note')->nullable();

            // Conversion tracking
            $table->foreignId('converted_to_order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};

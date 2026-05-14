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
        Schema::create('sms_recharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('sms_package_id')->constrained('sms_packages')->restrictOnDelete();
            $table->string('reference_no');        // SMS-0001
            $table->integer('sms_count');                   
            $table->decimal('price', 10, 2);               
            $table->decimal('rate_per_sms', 8, 4);           
            $table->string('payment_method')->nullable()->comment('cash, bank, bkash, nagad');
            $table->string('transaction_id')->nullable();    // bkash/bank transaction
            $table->string('account_number')->nullable();    // bkash/bank account
            $table->text('note')->nullable();
            $table->string('screenshot')->nullable();
            $table->tinyInteger('status')->default(Status::Pending->value);
            $table->text('reject_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_recharges');
    }
};

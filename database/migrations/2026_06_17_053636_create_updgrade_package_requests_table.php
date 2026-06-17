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
        Schema::create('updgrade_package_requests', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('pricing_package_id');
            $table->string('billing_cycle');
            $table->string('account_holder_name')->nullable();
            $table->string('payment_method');
            $table->string('number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('document_path')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->dateTime('request_date');
            $table->tinyInteger('status')->default(Status::Pending->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updgrade_package_requests');
    }
};

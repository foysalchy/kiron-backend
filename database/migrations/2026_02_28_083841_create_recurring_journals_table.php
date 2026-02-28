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
        Schema::create('recurring_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->date('start_date');
            $table->decimal('amount', 15, 2);
            $table->integer('repeat_interval')->comment('number');
            $table->string('interval_type')->comment(' Day, Week, Month, Year');
            $table->date('last_transaction_date')->nullable();
            $table->text('description');
            $table->string('approval_status')->default(Status::Draft->value)->comment('Draft, Approved');
            $table->string('operational_status')->default(Status::Inactive->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_journals');
    }
};

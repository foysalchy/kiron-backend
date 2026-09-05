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
        Schema::table('referral_partners', function (Blueprint $table) {
            $table->decimal('pending_balance', 12, 2)->default(0.00)->after('wallet_balance');
        });

        Schema::create('subscription_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('company_subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_refund_requests');
        
        Schema::table('referral_partners', function (Blueprint $table) {
            $table->dropColumn('pending_balance');
        });
    }
};

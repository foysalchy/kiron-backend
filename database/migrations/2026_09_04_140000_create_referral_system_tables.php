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
        // 1. Referral Groups Table
        Schema::create('referral_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('default_commission_rate', 5, 2)->default(20.00); // e.g. 20.00%
            $table->decimal('buyer_discount_rate', 5, 2)->default(5.00); // e.g. 5.00%
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('is_tiered')->default(true);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1); // 1: Active, 0: Inactive
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Referral Group Tiers (Sales Volume Ranks)
        Schema::create('referral_group_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_group_id')->constrained('referral_groups')->cascadeOnDelete();
            $table->string('tier_name')->nullable(); // e.g. "Bronze (1-50)", "Silver (51-100)", "Gold (101+)"
            $table->unsignedInteger('min_sales')->default(1);
            $table->unsignedInteger('max_sales')->nullable(); // null means unlimited (e.g. 301+)
            $table->decimal('commission_rate', 5, 2); // e.g. 25.00%
            $table->timestamps();
        });

        // 3. Referral Partners Table
        Schema::create('referral_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_group_id')->nullable()->constrained('referral_groups')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('referral_code', 64)->unique();
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->decimal('total_withdrawn', 12, 2)->default(0.00);
            $table->string('payout_method')->nullable(); // bank, bkash, nagad, rocket
            $table->json('payout_details')->nullable();
            $table->tinyInteger('status')->default(1); // 1: Active, 0: Inactive/Suspended
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Referral Attributions (Referred Companies)
        Schema::create('referral_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('referral_partners')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('referral_code_used', 64);
            $table->decimal('buyer_discount_rate', 5, 2)->default(0.00);
            $table->decimal('buyer_discount_amount', 12, 2)->default(0.00);
            $table->string('status')->default('registered'); // registered, subscribed_active, subscribed_inactive, churned
            $table->timestamps();
        });

        // 5. Referral Commissions (Commission Per Subscription/Payment)
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('referral_partners')->cascadeOnDelete();
            $table->foreignId('referral_attribution_id')->nullable()->constrained('referral_attributions')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('company_subscription_id')->nullable()->constrained('company_subscriptions')->nullOnDelete();
            $table->decimal('sale_amount', 12, 2)->default(0.00);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->decimal('commission_amount', 12, 2)->default(0.00);
            $table->string('tier_applied')->nullable();
            $table->string('status')->default('approved'); // approved, pending, reversed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Referral Withdrawals (Payout Requests)
        Schema::create('referral_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('referral_partners')->cascadeOnDelete();
            $table->string('request_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payout_method'); // bank, bkash, nagad, rocket
            $table->json('account_details')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_withdrawals');
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referral_attributions');
        Schema::dropIfExists('referral_partners');
        Schema::dropIfExists('referral_group_tiers');
        Schema::dropIfExists('referral_groups');
    }
};

<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\SiteSetting;
use App\Models\SmsWallet;
use App\Models\SmsWalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CheckoutOtpService
{
    protected static bool $columnsChecked = false;

    public function __construct(
        protected SmsSendService $smsSendService
    ) {}

    /**
     * Ensure database columns exist safely without breaking runtime.
     */
    public static function ensureColumnsExist(): void
    {
        if (self::$columnsChecked) {
            return;
        }
        self::$columnsChecked = true;

        try {
            if (Schema::hasTable('site_settings') && !Schema::hasColumn('site_settings', 'checkout_sms_verify')) {
                Schema::table('site_settings', function (Blueprint $table) {
                    $table->boolean('checkout_sms_verify')->default(false)->after('sms_forget_password');
                });
            }

            if (Schema::hasTable('orders')) {
                Schema::table('orders', function (Blueprint $table) {
                    if (!Schema::hasColumn('orders', 'is_otp_verified')) {
                        $table->boolean('is_otp_verified')->nullable()->default(null)->after('status');
                    }
                    if (!Schema::hasColumn('orders', 'otp_code')) {
                        $table->string('otp_code', 10)->nullable()->after('is_otp_verified');
                    }
                    if (!Schema::hasColumn('orders', 'otp_expires_at')) {
                        $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning("CheckoutOtpService auto-schema check: " . $e->getMessage());
        }
    }

    /**
     * Check if Checkout SMS / OTP Verification is enabled for company.
     */
    public function isOtpEnabled(?int $companyId = null): bool
    {
        if (!$companyId) {
            return false;
        }

        self::ensureColumnsExist();

        try {
            $setting = SiteSetting::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->select('id', 'company_id', 'checkout_sms_verify')
                ->first();

            return (bool) ($setting?->checkout_sms_verify ?? false);
        } catch (\Throwable $e) {
            Log::warning("isOtpEnabled check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if company has sufficient SMS credits.
     */
    public function hasSufficientBalance(?int $companyId = null): bool
    {
        if (!$companyId) {
            return false;
        }

        $wallet = SmsWallet::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->first();

        return (bool) ($wallet && $wallet->sms_count >= 1);
    }

    /**
     * Generate 4-digit OTP, attach note to incomplete order, deduct wallet, and send SMS.
     */
    public function sendOtp(Order $order, string $phone): array
    {
        $companyId = $order->company_id;

        // 1. Balance verification
        if (!$this->hasSufficientBalance($companyId)) {
            Log::warning("Checkout OTP skipped for Order #{$order->id}: Insufficient SMS balance.", [
                'company_id' => $companyId,
            ]);

            return [
                'success' => false,
                'error'   => 'insufficient_balance',
                'message' => 'দুঃখিত, এই মুহূর্তে ভেরিফিকেশন সার্ভিস অনুপলব্ধ (Insufficient SMS Balance)।',
            ];
        }

        // 2. Generate 4-digit OTP
        $otp = (string) random_int(1000, 9999);
        $expiresAt = now()->addMinutes(5);

        // 3. Mark as unverified and set note: 'Unverified'
        $order->update([
            'is_otp_verified' => false,
            'otp_code'        => $otp,
            'otp_expires_at'  => $expiresAt,
            'note'            => 'Unverified',
        ]);

        // Add or update system OrderNote
        $existingNote = OrderNote::withoutGlobalScopes()
            ->where('order_id', $order->id)
            ->whereIn('note', ['Checkout done but not verify', 'Unverified'])
            ->first();

        if (!$existingNote) {
            OrderNote::create([
                'company_id' => $companyId,
                'order_id'   => $order->id,
                'note'       => 'Unverified',
                'type'       => 'system',
                'status'     => 1,
            ]);
        }

        // 4. Deduct 1 SMS from Wallet
        $this->deductSmsBalance($companyId, $order);

        // 5. Send SMS via Gateway
        $shopName = SiteSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->value('shop_name') ?? config('app.name');

        $message = "আপনার {$shopName} অর্ডারের ভেরিফিকেশন ওটিপি কোড: {$otp}। এটি ৫ মিনিটের জন্য প্রযোজ্য।";

        try {
            $this->smsSendService->sendToGateway([$phone], $message);
        } catch (\Exception $e) {
            Log::error("Failed to send checkout OTP SMS for Order #{$order->id}: " . $e->getMessage());
        }

        $maskedPhone = substr($phone, 0, 3) . '****' . substr($phone, -4);

        return [
            'success'      => true,
            'requires_otp' => true,
            'order_id'     => $order->id,
            'phone'        => $maskedPhone,
            'message'      => 'আপনার নাম্বারে একটি ভেরিফিকেশন কোড (OTP) পাঠানো হয়েছে।',
        ];
    }

    /**
     * Verify customer OTP.
     */
    public function verifyOtp(int $orderId, string $otp, ?int $companyId = null): array
    {
        $order = Order::withoutGlobalScopes()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->find($orderId);

        if (!$order) {
            return [
                'success' => false,
                'message' => 'অর্ডারটি পাওয়া যায়নি।',
            ];
        }

        if ($order->is_otp_verified === true) {
            return [
                'success' => true,
                'message' => 'অর্ডারটি ইতিমধ্যেই ভেরিফাই করা হয়েছে।',
                'order'   => $order,
            ];
        }

        if ($order->otp_expires_at && now()->isAfter($order->otp_expires_at)) {
            return [
                'success' => false,
                'message' => 'ওটিপির মেয়াদ শেষ হয়ে গেছে। দয়া করে পুনরায় ওটিপি পাঠান।',
            ];
        }

        if (trim((string) $order->otp_code) !== trim((string) $otp)) {
            return [
                'success' => false,
                'message' => 'ভুল ওটিপি কোড! অনুগ্রহ করে সঠিক কোড দিন।',
            ];
        }

        // OTP Verified successfully!
        DB::transaction(function () use ($order, $otp) {
            $order->update([
                'is_otp_verified' => true,
                'otp_code'        => null,
                'otp_expires_at'  => null,
                'note'            => 'Verified',
                'status'          => Status::Pending->value,
            ]);

            // Remove or resolve any previous unverified notes for this order
            OrderNote::withoutGlobalScopes()
                ->where('order_id', $order->id)
                ->whereIn('note', ['Checkout done but not verify', 'Unverified'])
                ->delete();

            OrderNote::create([
                'company_id' => $order->company_id,
                'order_id'   => $order->id,
                'note'       => 'OTP Verified (' . $otp . ')',
                'type'       => 'system',
                'status'     => 1,
            ]);
        });

        return [
            'success' => true,
            'message' => 'অর্ডারটি সফলভাবে ভেরিফাই হয়েছে!',
            'order'   => $order->fresh(),
        ];
    }

    /**
     * Resend OTP code with rate-limit safeguard.
     */
    public function resendOtp(int $orderId, ?int $companyId = null): array
    {
        $order = Order::withoutGlobalScopes()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->find($orderId);

        if (!$order) {
            return [
                'success' => false,
                'message' => 'অর্ডারটি পাওয়া যায়নি।',
            ];
        }

        if ($order->is_otp_verified === true) {
            return [
                'success' => false,
                'message' => 'অর্ডারটি ইতিমধ্যেই ভেরিফাই করা হয়ে গেছে।',
            ];
        }

        $phone = $order->shipping_address['phone'] ?? $order->customer?->phone;
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'গ্রাহকের ফোন নাম্বার পাওয়া যায়নি।',
            ];
        }

        return $this->sendOtp($order, $phone);
    }

    /**
     * Deduct 1 SMS balance and write transaction ledger.
     */
    protected function deductSmsBalance(int $companyId, Order $order): void
    {
        try {
            DB::transaction(function () use ($companyId, $order) {
                $wallet = SmsWallet::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->lockForUpdate()
                    ->first();

                if ($wallet && $wallet->sms_count >= 1) {
                    $balanceBefore = $wallet->sms_count;
                    $balanceAfter  = $balanceBefore - 1;

                    $wallet->update(['sms_count' => $balanceAfter]);

                    SmsWalletTransaction::create([
                        'company_id'     => $companyId,
                        'sms_wallet_id'  => $wallet->id,
                        'type'           => 'deduct',
                        'sms_count'      => 1,
                        'rate_per_sms'   => $wallet->currentRate(),
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceAfter,
                        'note'           => 'Checkout OTP SMS for Order #' . ($order->order_no ?? $order->id),
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to deduct SMS balance for Order #{$order->id}: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Services;

use App\Enums\Status;
use App\Helpers\NetworkHelper;
use App\Models\IpDirectory;
use App\Models\IpSetting;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\SiteSetting;
use App\Models\SmsWallet;
use App\Models\SmsWalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
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
        $clientIp = NetworkHelper::getClientIp();

        // 0. Fraud Protection: Blocked IP check
        $isIpBlocked = IpDirectory::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('ip_address', $clientIp)
            ->where('status', 0) // 0 = Blocked
            ->exists();

        if ($isIpBlocked) {
            Log::warning("Checkout OTP rejected: IP {$clientIp} is blocked in directory for company {$companyId}");
            return [
                'success' => false,
                'error'   => 'ip_blocked',
                'message' => 'দুঃখিত, আপনার নেটওয়ার্ক থেকে এই মুহূর্তে ওটিপি পাঠানো স্থগিত রয়েছে। হেল্পলাইনে যোগাযোগ করুন।',
            ];
        }

        // 1. Balance verification
        if (!$this->hasSufficientBalance($companyId)) {
            Log::warning("Checkout OTP skipped for Order #{$order->id}: Insufficient SMS balance.", [
                'company_id' => $companyId,
            ]);

            return [
                'success'          => false,
                'error'            => 'insufficient_balance',
                'is_balance_error' => true,
                'message'          => 'দুঃখিত, এই মুহূর্তে ভেরিফিকেশন সার্ভিস অনুপলব্ধ (Insufficient SMS Balance)।',
            ];
        }

        // 1.1 Fraud Rules: SMS Rate Limiting per Phone and IP
        IpSetting::ensureColumnsExist();
        $ipSetting = IpSetting::withoutGlobalScopes()->where('company_id', $companyId)->first();
        $smsLimitMinutes = (int) ($ipSetting?->sms_limit_minutes ?: 5);
        $smsLimitCount   = (int) ($ipSetting?->sms_limit_count ?: 3);

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }

        $phoneCacheKey = "otp_limit_phone_{$companyId}_{$cleanPhone}";
        $ipCacheKey    = "otp_limit_ip_{$companyId}_{$clientIp}";

        $phoneAttempts = (int) Cache::get($phoneCacheKey, 0);
        $ipAttempts    = (int) Cache::get($ipCacheKey, 0);

        if ($phoneAttempts >= $smsLimitCount) {
            Log::warning("Checkout OTP rate-limited for phone {$cleanPhone}: exceeded {$smsLimitCount} SMS in {$smsLimitMinutes} mins.");
            return [
                'success' => false,
                'error'   => 'sms_limit_exceeded',
                'message' => "আপনি ইতিমধ্যে অতিরিক্ত বার ওটিপি চেষ্টা করেছেন। অনুগ্রহ করে {$smsLimitMinutes} মিনিট পর আবার চেষ্টা করুন।",
            ];
        }

        $maxIpAttempts = max(5, $smsLimitCount * 2);
        if ($ipAttempts >= $maxIpAttempts) {
            Log::warning("Checkout OTP rate-limited for IP {$clientIp}: exceeded {$maxIpAttempts} SMS in {$smsLimitMinutes} mins.");
            return [
                'success' => false,
                'error'   => 'ip_sms_limit_exceeded',
                'message' => "আপনার নেটওয়ার্ক থেকে অতিরিক্ত ওটিপি রিকোয়েস্ট করা হয়েছে। অনুগ্রহ করে {$smsLimitMinutes} মিনিট পর চেষ্টা করুন।",
            ];
        }

        // 2. Generate 4-digit OTP
        $otp = (string) random_int(1000, 9999);
        $expiresAt = now()->addMinutes(5);

        // 3. Mark as unverified and set note: 'Not Verified'
        $order->update([
            'is_otp_verified' => false,
            'otp_code'        => $otp,
            'otp_expires_at'  => $expiresAt,
            'note'            => 'Not Verified',
        ]);

        // Add or update system OrderNote
        $existingNote = OrderNote::withoutGlobalScopes()
            ->where('order_id', $order->id)
            ->whereIn('note', ['Checkout done but not verify', 'Unverified', 'Not Verified'])
            ->first();

        if (!$existingNote) {
            OrderNote::create([
                'company_id' => $companyId,
                'order_id'   => $order->id,
                'note'       => 'Not Verified',
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
            // Record rate limit attempts on send
            Cache::put($phoneCacheKey, $phoneAttempts + 1, now()->addMinutes($smsLimitMinutes));
            Cache::put($ipCacheKey, $ipAttempts + 1, now()->addMinutes($smsLimitMinutes));
        } catch (\Exception $e) {
            $errMsg = $e->getMessage();
            Log::error("Failed to send checkout OTP SMS for Order #{$order->id}: " . $errMsg);
            $this->refundSmsBalance($companyId, $order);

            $isBalanceError = str_contains(strtolower($errMsg), 'balance') 
                || str_contains(strtolower($errMsg), 'not enough')
                || str_contains(strtolower($errMsg), '1007');

            return [
                'success'          => false,
                'error'            => $isBalanceError ? 'gateway_insufficient_balance' : 'gateway_error',
                'is_balance_error' => true,
                'message'          => 'এসএমএস গেটওয়ে ব্যালেন্স শেষ হওয়ায় ওটিপি পাঠানো সম্ভব হয়নি (' . $errMsg . ')।',
            ];
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
                ->whereIn('note', ['Checkout done but not verify', 'Unverified', 'Not Verified'])
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

    /**
     * Refund 1 SMS balance if gateway sending fails.
     */
    protected function refundSmsBalance(int $companyId, Order $order): void
    {
        try {
            DB::transaction(function () use ($companyId, $order) {
                $wallet = SmsWallet::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->lockForUpdate()
                    ->first();

                if ($wallet) {
                    $balanceBefore = $wallet->sms_count;
                    $balanceAfter  = $balanceBefore + 1;

                    $wallet->update(['sms_count' => $balanceAfter]);

                    SmsWalletTransaction::create([
                        'company_id'     => $companyId,
                        'sms_wallet_id'  => $wallet->id,
                        'type'           => 'refund',
                        'sms_count'      => 1,
                        'rate_per_sms'   => $wallet->currentRate(),
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceAfter,
                        'note'           => 'Refund: OTP SMS send failed for Order #' . ($order->order_no ?? $order->id),
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to refund SMS balance for Order #{$order->id}: " . $e->getMessage());
        }
    }
}

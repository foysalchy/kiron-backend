<?php

namespace App\Services;

use App\Models\ReferralPartner;
use Illuminate\Support\Facades\{DB, Hash, Log, Mail, RateLimiter};
use Carbon\Carbon;

class PartnerPasswordResetService
{
    protected function otpRateLimitKey(string $identifier): string
    {
        return 'partner_password_otp_attempts_' . sha1($identifier);
    }

    public function requestOtp(string $method, string $identifier): array
    {
        $rateLimitKey = $this->otpRateLimitKey($identifier);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            throw new \Exception(
                "Too many attempts. Please try again in {$minutes} minute(s)."
            );
        }

        $partner = null;
        if ($method === 'email') {
            $partner = ReferralPartner::where('email', $identifier)->first();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
            $partner = ReferralPartner::where('phone', $identifier)
                ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                    $q->orWhere('phone', 'like', '%' . substr($cleanPhone, -10));
                })
                ->first();
        }

        if (!$partner) {
            $notFoundMessage = $method === 'email'
                ? 'No partner account found with that email address.'
                : 'No partner account found with that phone number.';

            throw new \Exception($notFoundMessage);
        }

        RateLimiter::hit($rateLimitKey, 600);

        // Remove previous tokens for this identifier
        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->insert([
            'email'      => $identifier,
            'token'      => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        if ($method === 'email') {
            try {
                Mail::to($partner->email)->send(new \App\Mail\CustomerResetPasswordOtpMail($partner->name, $partner->email, $otp));
            } catch (\Exception $e) {
                Log::error('Partner password reset email failed: ' . $e->getMessage());
            }
        } else {
            try {
                $targetPhone = $partner->phone ?: $identifier;
                $message = "Your Dorja Partner Portal password reset code is: {$otp}. Valid for 10 minutes.";
                app(SmsSendService::class)->sendToGateway([$targetPhone], $message);
            } catch (\Exception $e) {
                Log::error('Partner password reset SMS failed: ' . $e->getMessage());
            }
        }

        Log::info("Partner password reset OTP sent to {$identifier} via {$method}");

        $successMessage = $method === 'email'
            ? 'A 6-digit verification code has been sent to your email.'
            : 'A 6-digit verification code has been sent to your phone number.';

        return ['message' => $successMessage, 'partner' => $partner];
    }

    public function verifyOtpAndReset(string $method, string $identifier, string $otp, string $newPassword): void
    {
        $partner = null;
        if ($method === 'email') {
            $partner = ReferralPartner::where('email', $identifier)->first();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
            $partner = ReferralPartner::where('phone', $identifier)
                ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                    $q->orWhere('phone', 'like', '%' . substr($cleanPhone, -10));
                })
                ->first();
        }

        if (!$partner) {
            throw new \Exception('Invalid request or partner account not found.');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $identifier)
            ->first();

        if (!$record) {
            throw new \Exception('OTP has expired or is invalid. Please request a new code.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $identifier)->delete();
            throw new \Exception('OTP has expired. Please request a new code.');
        }

        if (!Hash::check($otp, $record->token)) {
            throw new \Exception('Invalid verification code.');
        }

        $partner->update([
            'password' => Hash::make($newPassword),
        ]);

        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        $this->clearRateLimit($identifier);

        Log::info("Partner password reset successfully for partner ID {$partner->id}");
    }

    public function clearRateLimit(string $identifier): void
    {
        RateLimiter::clear($this->otpRateLimitKey($identifier));
    }
}

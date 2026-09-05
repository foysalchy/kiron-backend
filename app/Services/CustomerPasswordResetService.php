<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Party;
use Illuminate\Support\Facades\{DB, Hash, Log, Mail, RateLimiter};
use Carbon\Carbon;

class CustomerPasswordResetService
{
    protected function otpRateLimitKey(string $identifier): string
    {
        return 'customer_password_otp_attempts_' . sha1($identifier);
    }

    public function requestOtp(string $method, string $identifier): array
    {
        $rateLimitKey = $this->otpRateLimitKey($identifier);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            throw ApiException::badRequest(
                "Too many attempts. Please try again in {$minutes} minute(s)."
            );
        }

        $field = $method === 'email' ? 'email' : 'phone';
        $party = Party::where($field, $identifier)->first();

        if (!$party) {
            $notFoundMessage = $method === 'email'
                ? 'No account found with that email address.'
                : 'No account found with that phone number.';

            throw ApiException::badRequest($notFoundMessage);
        }

        RateLimiter::hit($rateLimitKey, 600);

        // বিদ্যমান password_reset_tokens টেবিলের 'email' কলাম generic identifier হিসেবে ব্যবহার হচ্ছে
        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        $otp = (string) random_int(100000, 999999);
        Log::info("Generated OTP for {$identifier}: {$otp}");

        DB::table('password_reset_tokens')->insert([
            'email'      => $identifier,
            'token'      => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        if ($method === 'email') {
            Mail::to($party->email)->send(new \App\Mail\CustomerResetPasswordOtpMail($party->name, $party->email, $otp));
        } else {
            $message = "Your password reset code is: {$otp}. Valid for 10 minutes.";
            app(\App\Services\SmsSendService::class)->sendToGateway([$party->phone], $message);
        }

        Log::info("Customer password reset OTP sent to {$identifier} via {$method}");

        $successMessage = $method === 'email'
            ? 'A code has been sent to your email.'
            : 'A code has been sent to your phone.';

        return ['message' => $successMessage];
    }

    public function verifyOtpAndReset(string $method, string $identifier, string $otp, string $newPassword): void
    {
        $field = $method === 'email' ? 'email' : 'phone';
        $party = Party::where($field, $identifier)->first();

        if (!$party) {
            throw ApiException::badRequest('Invalid request');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $identifier)
            ->first();

        if (!$record) {
            throw ApiException::badRequest('OTP has expired. Please request a new one.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $identifier)->delete();
            throw ApiException::badRequest('OTP has expired. Please request a new one.');
        }

        if (!Hash::check($otp, $record->token)) {
            throw ApiException::forbidden('Invalid OTP');
        }

        $party->update([
            'password' => Hash::make($newPassword),
        ]);

        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        $this->clearRateLimit($identifier);

        Log::info("Customer password reset successfully for party ID {$party->id}");
    }

    public function clearRateLimit(string $identifier): void
    {
        RateLimiter::clear($this->otpRateLimitKey($identifier));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegistration\{StoreBasicRegistrationRequest, StoreSubscriptionRequest, VerifyOtpRequest, ResendOtpRequest, StoreBasicSettingsRequest};
use App\Services\CompanyRegistrationService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UnifiedSellerRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CompanyRegistrationController extends Controller
{
    public function __construct(
        protected CompanyRegistrationService $registrationService
    ) {}

    public function pricings(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user();
        $companyId = $user?->company_id ?? $request->get('company_id') ?? $request->get('registration_id');

        $attribution = null;
        if ($companyId) {
            $attribution = \App\Models\ReferralAttribution::where('company_id', $companyId)->first();
        }

        $rate = 0;
        $referralCode = null;

        if ($attribution) {
            $rate = (float)($attribution->buyer_discount_rate ?? 0);
            $referralCode = $attribution->referral_code_used ?? null;

            if ($rate <= 0 && isset($attribution->referral_partner_id)) {
                $partner = \App\Models\ReferralPartner::with('group')->find($attribution->referral_partner_id);
                if ($partner && $partner->group) {
                    $rate = (float)($partner->group->buyer_discount_rate ?? 0);
                    $referralCode = $referralCode ?: $partner->referral_code;
                }
            }
        }

        if ($rate <= 0) {
            $refCode = $request->get('ref') ?? $request->get('referral') ?? $request->cookie('dorja_ref');
            if ($refCode) {
                $validation = app(\App\Services\ReferralService::class)->validateReferralCode($refCode);
                if ($validation) {
                    $rate = (float)($validation['buyer_discount_rate'] ?? 0);
                    $referralCode = $validation['referral_code'] ?? $refCode;
                }
            }
        }

        $plans = $this->registrationService->getActivePricings();

        if ($rate > 0 && $referralCode) {
            foreach ($plans as $plan) {
                $plan->referral_code = $referralCode;
                $plan->buyer_discount_rate = $rate;
                if ($plan->tiers) {
                    foreach ($plan->tiers as $tier) {
                        $basePrice = ($tier->discount_price > 0 && $tier->discount_price < $tier->regular_price)
                            ? (float)$tier->discount_price
                            : (float)$tier->regular_price;
                        $discountAmount = round(($basePrice * $rate) / 100, 2);
                        $tier->referral_discount_amount = $discountAmount;
                        $tier->final_price_with_referral = max(0, round($basePrice - $discountAmount, 2));
                        $tier->referral_discount_rate = $rate;
                    }
                }
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Pricing plans fetched successfully',
            'data'     => $plans,
            'referral' => ($rate > 0 && $referralCode) ? [
                'code'                => $referralCode,
                'buyer_discount_rate' => $rate,
            ] : null,
        ]);
    }

    public function storeBasic(StoreBasicRegistrationRequest $request): JsonResponse
    {
        $result = $this->registrationService->registerBasic($request->validated());
        return ResponseHelper::created($result, 'Account created successfully.');
    }

    public function storeSubscription(StoreSubscriptionRequest $request): JsonResponse
    {
        $this->registrationService->registerSubscription($request->validated());
        return ResponseHelper::success(null, 'Subscription saved. OTP sent to your email.');
    }
    public function storeBasicSettings(StoreBasicSettingsRequest $request): JsonResponse
    {
        $data = $this->registrationService->registerBasicSettings($request->validated());
        return ResponseHelper::success($data, 'Basic settings saved.');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = $this->registrationService->verifyOtp(
            $request->integer('registration_id'),
            $request->string('type'),
            $request->string('otp'),
            $request->string('method')
        );

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'data' => [
                'user' => $user,
            ],
        ]);
    }


    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $registrationId = $request->integer('registration_id');
        $type = $request->string('type');

        // $rateLimitKey = "resend-otp:{$registrationId}:{$type}";

        // if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        //     $seconds = RateLimiter::availableIn($rateLimitKey);
        //     $formattedTime = $this->formatWaitTime($seconds);

        //     throw ApiException::badRequest(
        //         "Too many OTP resend attempts. Please try again after {$formattedTime}."
        //     );
        // }

        // RateLimiter::hit($rateLimitKey, 600); // 600 seconds = 10 minutes

        $this->registrationService->resendOtp(
            $registrationId,
            $type,
            $request->string('method'),
        );

        return ResponseHelper::success(null, 'OTP resent successfully');
    }
    private function formatWaitTime(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0 && $remainingSeconds > 0) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '')
                . " {$remainingSeconds} second" . ($remainingSeconds > 1 ? 's' : '');
        }

        if ($minutes > 0) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        }

        return "{$remainingSeconds} second" . ($remainingSeconds > 1 ? 's' : '');
    }
    public function register(UnifiedSellerRegistrationRequest $request): JsonResponse
    {
        $result = $this->registrationService->registerSeller($request->all(), $request);
        return ResponseHelper::success($result, 'Seller registration and store configuration completed successfully.');
    }
}

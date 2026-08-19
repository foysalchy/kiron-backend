<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegistration\{StoreBasicRegistrationRequest, StoreSubscriptionRequest, VerifyOtpRequest, ResendOtpRequest, StoreBasicSettingsRequest};
use App\Services\CompanyRegistrationService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UnifiedSellerRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class CompanyRegistrationController extends Controller
{
    public function __construct(
        protected CompanyRegistrationService $registrationService
    ) {}

    public function pricings(): JsonResponse
    {
        $plans = $this->registrationService->getActivePricings();
        return ResponseHelper::success($plans, 'Pricing plans fetched successfully');
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

        $rateLimitKey = "resend-otp:{$registrationId}:{$type}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $formattedTime = $this->formatWaitTime($seconds);

            throw ApiException::badRequest(
                "Too many OTP resend attempts. Please try again after {$formattedTime}."
            );
        }

        RateLimiter::hit($rateLimitKey, 600); // 600 seconds = 10 minutes

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

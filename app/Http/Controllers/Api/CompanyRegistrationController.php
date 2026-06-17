<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegistration\{StoreBasicRegistrationRequest, StoreSubscriptionRequest, VerifyOtpRequest, ResendOtpRequest,StoreBasicSettingsRequest};
use App\Services\CompanyRegistrationService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\UnifiedSellerRegistrationRequest;
use Illuminate\Http\JsonResponse;

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
        $data=$this->registrationService->registerBasicSettings($request->validated());
        return ResponseHelper::success($data, 'Basic settings saved.');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $this->registrationService->verifyOtp(
            $request->integer('registration_id'),
            $request->string('type'),
            $request->string('otp')
        );

        return ResponseHelper::success(null, "Email verified successfully.");
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->registrationService->resendOtp(
            $request->integer('registration_id'),
            $request->string('type')
        );

        return ResponseHelper::success(null, "New OTP sent to your email.");
    }
      public function register(UnifiedSellerRegistrationRequest $request): JsonResponse
    {
        $result = $this->registrationService->registerSeller($request->all(), $request);
        return ResponseHelper::success($result, 'Seller registration and store configuration completed successfully.');
    }
}
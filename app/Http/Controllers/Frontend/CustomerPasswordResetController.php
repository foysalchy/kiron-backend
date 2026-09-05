<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CustomerPasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerPasswordResetController extends FrontendController
{
    protected CustomerPasswordResetService $passwordResetService;

    public function __construct(CustomerPasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function showForgotPasswordForm()
    {
        return  $this->view('frontend.user.forgot-password');
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'method'     => ['required', 'in:email,sms'],
            'identifier' => ['required', 'string'],
        ]);

        try {
            $data = $this->passwordResetService->requestOtp($request->method, $request->identifier);
     
            return response()->json(array_merge(['status' => 'success'], $data));
        } catch (\App\Exceptions\ApiException $e) {
            Log::error('Error occurred while requesting OTP', ['message' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'method'       => ['required', 'in:email,sms'],
            'identifier'   => ['required', 'string'],
            'otp'          => ['required', 'digits:6'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->passwordResetService->verifyOtpAndReset(
                $request->method,
                $request->identifier,
                $request->otp,
                $request->new_password,
            );

            return response()->json(['status' => 'success', 'message' => 'Password reset successfully']);
        } catch (\App\Exceptions\ApiException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}

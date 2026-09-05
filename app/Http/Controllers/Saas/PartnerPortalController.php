<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\ReferralPartner;
use App\Models\ReferralGroup;
use App\Services\PartnerPasswordResetService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PartnerPortalController extends Controller
{
    protected ReferralService $referralService;
    protected PartnerPasswordResetService $passwordResetService;

    public function __construct(ReferralService $referralService, PartnerPasswordResetService $passwordResetService)
    {
        $this->referralService = $referralService;
        $this->passwordResetService = $passwordResetService;
    }

    protected function getAuthenticatedPartner()
    {
        $partnerId = session('partner_id');
        if (!$partnerId) {
            return null;
        }
        $partner = ReferralPartner::with('group.tiers')->find($partnerId);
        if ($partner && !$partner->group) {
            $defaultGroup = ReferralGroup::with('tiers')->whereIn('status', [1, '1', 'active'])->first();
            if ($defaultGroup) {
                $partner->referral_group_id = $defaultGroup->id;
                $partner->save();
                $partner->setRelation('group', $defaultGroup);
            }
        }
        return $partner;
    }

    public function showLoginForm()
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }
        return view('saas.partner.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $partner = ReferralPartner::where('email', $request->email)->first();

        if (!$partner || !Hash::check($request->password, $partner->password)) {
            return back()->withErrors(['email' => 'Invalid email or password'])->withInput();
        }

        if (!in_array($partner->status, [1, '1', 'active'])) {
            return back()->withErrors(['email' => 'Your partner account is suspended or inactive. Please contact support.'])->withInput();
        }

        session(['partner_id' => $partner->id, 'partner_name' => $partner->name]);

        return redirect()->route('partner.dashboard')->with('success', 'Welcome back, ' . $partner->name . '!');
    }

    public function showForgotPasswordForm()
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }
        return view('saas.partner.forgot-password');
    }

    public function requestPasswordResetOtp(Request $request)
    {
        $request->validate([
            'method'     => 'required|in:email,sms',
            'identifier' => 'required|string',
        ]);

        try {
            $data = $this->passwordResetService->requestOtp($request->method, trim($request->identifier));

            return redirect()->route('partner.password.reset', [
                'method'     => $request->method,
                'identifier' => trim($request->identifier),
            ])->with('success', $data['message']);
        } catch (\Exception $e) {
            return back()->withErrors(['identifier' => $e->getMessage()])->withInput();
        }
    }

    public function showResetPasswordForm(Request $request)
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }

        $method = $request->query('method', 'email');
        $identifier = $request->query('identifier', '');

        if (empty($identifier)) {
            return redirect()->route('partner.password.forgot');
        }

        return view('saas.partner.reset-password', compact('method', 'identifier'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'method'                => 'required|in:email,sms',
            'identifier'            => 'required|string',
            'otp'                   => 'required|digits:6',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        try {
            $this->passwordResetService->verifyOtpAndReset(
                $request->method,
                trim($request->identifier),
                trim($request->otp),
                $request->password
            );

            return redirect()->route('partner.login')->with('success', 'Your password has been reset successfully! You can now log in.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()])->withInput();
        }
    }

    public function showRegisterForm()
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }
        $groups = ReferralGroup::whereIn('status', [1, '1', 'active'])->get();
        $pending = session('pending_partner_registration', []);
        return view('saas.partner.register', compact('groups', 'pending'));
    }

    public function register(Request $request)
    {
        $verifyMethod = $request->input('verify_method', 'email');

        $rules = [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:referral_partners,email',
            'verify_method'     => 'required|in:email,sms',
            'password'          => 'required|string|min:6|confirmed',
            'referral_group_id' => 'nullable|exists:referral_groups,id',
        ];

        if ($verifyMethod === 'sms') {
            $rules['phone'] = 'required|string|min:10|max:50';
        } else {
            $rules['phone'] = 'nullable|string|max:50';
        }

        $request->validate($rules, [
            'phone.required' => 'A valid phone number is required for SMS verification.',
        ]);

        try {
            $otp = (string) random_int(100000, 999999);
            $email = trim($request->email);
            $phone = $request->phone ? trim($request->phone) : null;
            $identifier = $verifyMethod === 'sms' ? $phone : $email;

            // Store OTP token in password_reset_tokens
            DB::table('password_reset_tokens')->where('email', $identifier)->delete();
            DB::table('password_reset_tokens')->insert([
                'email'      => $identifier,
                'token'      => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]);

            // Store pending registration data in session
            session([
                'pending_partner_registration' => [
                    'name'              => $request->name,
                    'email'             => $email,
                    'phone'             => $phone,
                    'password'          => $request->password,
                    'referral_group_id' => $request->referral_group_id,
                    'verify_method'     => $verifyMethod,
                    'identifier'        => $identifier,
                ]
            ]);

            // Dispatch OTP via chosen channel
            if ($verifyMethod === 'sms') {
                $message = "Your Dorja Partner verification code is: {$otp}. Valid for 10 minutes.";
                app(SmsSendService::class)->sendToGateway([$phone], $message);
                $destination = 'SMS (' . $phone . ')';
            } else {
                Mail::to($email)->send(new \App\Mail\CustomerResetPasswordOtpMail($request->name, $email, $otp));
                $destination = 'Email (' . $email . ')';
            }

            return redirect()->route('partner.register.verify-form')
                ->with('success', 'A 6-digit verification code has been sent via ' . $destination . '. Please enter it to complete your registration.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send verification code: ' . $e->getMessage()])->withInput();
        }
    }

    public function showVerifyEmailForm()
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }

        $pending = session('pending_partner_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('partner.register')->withErrors(['email' => 'Please fill in the registration form first.']);
        }

        $verifyMethod = $pending['verify_method'] ?? 'email';
        $identifier = $pending['identifier'] ?? ($verifyMethod === 'sms' ? $pending['phone'] : $pending['email']);
        $email = $pending['email'];
        $phone = $pending['phone'] ?? null;

        return view('saas.partner.verify-email', compact('pending', 'verifyMethod', 'identifier', 'email', 'phone'));
    }

    public function verifyEmailAndRegister(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pending = session('pending_partner_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('partner.register')->withErrors(['email' => 'Session expired. Please fill in the registration form again.']);
        }

        $verifyMethod = $pending['verify_method'] ?? 'email';
        $identifier = $pending['identifier'] ?? ($verifyMethod === 'sms' ? ($pending['phone'] ?? '') : $pending['email']);
        
        $record = DB::table('password_reset_tokens')->where('email', $identifier)->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Verification code has expired or is invalid. Please request a new one.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $identifier)->delete();
            return back()->withErrors(['otp' => 'Verification code expired. Please request a new one.']);
        }

        if (!Hash::check(trim($request->otp), $record->token)) {
            return back()->withErrors(['otp' => 'Invalid verification code. Please check and try again.']);
        }

        try {
            // Create verified partner account
            $partner = $this->referralService->registerPartner($pending);

            // Clean up OTP & session
            DB::table('password_reset_tokens')->where('email', $identifier)->delete();
            session()->forget('pending_partner_registration');

            // Auto-login
            session(['partner_id' => $partner->id, 'partner_name' => $partner->name]);

            return redirect()->route('partner.dashboard')
                ->with('success', 'Congratulations! Your partner account has been verified and created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function resendRegisterOtp(Request $request)
    {
        $pending = session('pending_partner_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('partner.register');
        }

        $method = $request->input('method', $pending['verify_method'] ?? 'email');
        
        if ($method === 'sms' && empty($pending['phone'])) {
            return back()->withErrors(['otp' => 'No phone number provided. Please edit registration details to add a phone number.']);
        }

        $identifier = $method === 'sms' ? $pending['phone'] : $pending['email'];
        $otp = (string) random_int(100000, 999999);

        // Delete old token and insert new
        DB::table('password_reset_tokens')->where('email', $identifier)->delete();
        if ($pending['identifier'] && $pending['identifier'] !== $identifier) {
            DB::table('password_reset_tokens')->where('email', $pending['identifier'])->delete();
        }

        DB::table('password_reset_tokens')->insert([
            'email'      => $identifier,
            'token'      => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        // Update session
        $pending['verify_method'] = $method;
        $pending['identifier'] = $identifier;
        session(['pending_partner_registration' => $pending]);

        try {
            if ($method === 'sms') {
                $message = "Your Dorja Partner verification code is: {$otp}. Valid for 10 minutes.";
                app(SmsSendService::class)->sendToGateway([$pending['phone']], $message);
                $channelName = "SMS to " . $pending['phone'];
            } else {
                Mail::to($pending['email'])->send(new \App\Mail\CustomerResetPasswordOtpMail($pending['name'] ?? 'Partner', $pending['email'], $otp));
                $channelName = "Email to " . $pending['email'];
            }

            return back()->with('success', 'A new 6-digit verification code has been sent via ' . $channelName);
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => 'Failed to resend code: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        session()->forget(['partner_id', 'partner_name']);
        return redirect()->route('partner.login')->with('success', 'You have been logged out successfully.');
    }

    public function dashboard()
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $stats = $this->referralService->getPartnerDashboardStats($partner->id);
        $recentAttributions = $partner->attributions()->with('company')->latest()->take(5)->get();
        $recentCommissions = $partner->commissions()->with('company')->latest()->take(5)->get();

        return view('saas.partner.dashboard', compact('partner', 'stats', 'recentAttributions', 'recentCommissions'));
    }

    public function referrals()
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $referrals = $partner->attributions()->with(['company', 'commissions'])->latest()->paginate(15);

        return view('saas.partner.referrals', compact('partner', 'referrals'));
    }

    public function earnings()
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $commissions = $partner->commissions()->with('company')->latest()->paginate(15);

        return view('saas.partner.earnings', compact('partner', 'commissions'));
    }

    public function withdrawals()
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $withdrawals = $partner->withdrawals()->latest()->paginate(15);

        return view('saas.partner.withdrawals', compact('partner', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request)
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $request->validate([
            'amount' => 'required|numeric|min:500',
            'payment_method' => 'required|in:bkash,nagad,rocket,bank_transfer',
            'account_details' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $this->referralService->requestWithdrawal(
                $partner->id,
                (float)$request->amount,
                $request->payment_method,
                $request->account_details,
                $request->note
            );

            return back()->with('success', 'Withdrawal request submitted successfully! We will review and process it shortly.');
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    public function profile()
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        return view('saas.partner.profile', compact('partner'));
    }

    public function updateProfile(Request $request)
    {
        $partner = $this->getAuthenticatedPartner();
        if (!$partner) {
            return redirect()->route('partner.login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'payout_method' => 'nullable|string|max:50',
            'payout_details' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $partner->name = $request->name;
        $partner->phone = $request->phone;
        $partner->payout_method = $request->payout_method;
        $partner->payout_details = $request->payout_details;

        if ($request->filled('password')) {
            $partner->password = Hash::make($request->password);
        }

        $partner->save();

        return back()->with('success', 'Profile updated successfully!');
    }
}

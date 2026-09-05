<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\ReferralPartner;
use App\Models\ReferralGroup;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PartnerPortalController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    protected function getAuthenticatedPartner()
    {
        $partnerId = session('partner_id');
        if (!$partnerId) {
            return null;
        }
        return ReferralPartner::with('group.tiers')->find($partnerId);
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

        if ($partner->status !== 'active') {
            return back()->withErrors(['email' => 'Your partner account is suspended or inactive. Please contact support.'])->withInput();
        }

        session(['partner_id' => $partner->id, 'partner_name' => $partner->name]);

        return redirect()->route('partner.dashboard')->with('success', 'Welcome back, ' . $partner->name . '!');
    }

    public function showRegisterForm()
    {
        if (session('partner_id')) {
            return redirect()->route('partner.dashboard');
        }
        $groups = ReferralGroup::where('status', 'active')->get();
        return view('saas.partner.register', compact('groups'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:referral_partners,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
            'referral_group_id' => 'nullable|exists:referral_groups,id',
        ]);

        try {
            $partner = $this->referralService->registerPartner($request->all());
            session(['partner_id' => $partner->id, 'partner_name' => $partner->name]);

            return redirect()->route('partner.dashboard')->with('success', 'Your partner account has been created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
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

<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        return view($template . '.frontend.user.register');
    }

    // user registration
    public function storeRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:parties,email', // টেবিল নাম parties
            'phone'    => 'required|string|max:20|unique:parties,phone',
            'address'  => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $company = getCurrentCompany();

        $user = Party::create([
            'company_id' => $company->id,
            'type'       => Party::TYPE_CUSTOMER,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'password'   => Hash::make($request->password),
            'status'     => true,
        ]);

        Auth::guard('customer')->login($user);

        return redirect()->route('user.dashboard')->with('success', 'নিবন্ধন সফল হয়েছে।');
    }

    public function login()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        return view($template . '.frontend.user.login');
    }
    // login user
    public function storeLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'type' => Party::TYPE_CUSTOMER
        ];

        if (Auth::guard('customer')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'))->with('success', 'লগইন সফল হয়েছে।');
        }

        throw ValidationException::withMessages([
            'email' => ['ইমেইল বা পাসওয়ার্ড সঠিক নয়।'],
        ]);
    }

    // logout function
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'লগআউট সফল হয়েছে।');
    }
    public function profile()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;
        $user = auth('customer')->user();

        return view($template . '.frontend.user.profile', compact('user'));
    }
    // update your profile
    public function updateProfile(Request $request)
    {
        $user = auth('customer')->user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:parties,email,' . $user->id,
            'phone'   => 'required|string|max:20',
            'address' => 'nullable|string',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('name', 'email', 'phone', 'address');

        if ($request->hasFile('profile')) {
            if ($user->profile) {
                Storage::disk('public')->delete($user->profile);
            }
            $data['profile'] = $request->file('profile')->store('customers/profiles', 'public');
        }

        $user->update($data);

        return back()->with('success', 'আপনার প্রোফাইল সফলভাবে আপডেট করা হয়েছে!');
    }

    // update your password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('customer')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!');
    }
    public function dashboard()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $user = Auth::guard('customer')->user();

        $allOrders = $user->orders()->with('orderDetails.product')->latest()->get();
        $recentOrders = $allOrders->take(5);

        $wishlistItems = Wishlist::where('company_id', $company->id)
            ->with(['product.variations', 'product.brand'])
            ->latest()
            ->get();

        $totalOrders = $allOrders->count();
        $totalSpent = $allOrders->where('status', 'delivered')->sum('grand_total');
        $wishlistCount = $wishlistItems->count();

        return view($template . '.frontend.user.dashboard', compact(
            'user',
            'totalOrders',
            'totalSpent',
            'wishlistCount',
            'recentOrders',
            'allOrders',
            'wishlistItems',

        ));
    }
}

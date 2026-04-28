<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerPaymentMethod;
use App\Models\Party;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends FrontendController
{
    public function register()
    {
        return  $this->view('frontend.user.register');
    }

    // user registration
    public function storeRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:parties,email', // parties
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
            'status'     => Status::Active->value,
        ]);

        Auth::guard('customer')->login($user);

        return redirect()->route('user.dashboard')->with('success', 'Registration was successful. Welcome to our store!');
    }

    public function login()
    {
        return  $this->view('frontend.user.login');
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

            $user = Auth::guard('customer')->user();

            if ($user->status == Status::Pending->value) {
                $user->status = Status::Active->value;
                $user->save();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'))->with('success', 'Login successful. Welcome back!');
        }

        throw ValidationException::withMessages([
            'email' => ['Email or password is incorrect.'],
        ]);
    }

    // logout function
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout was successful.');
    }
    public function profile()
    {
        $user = auth('customer')->user();

        return  $this->view('frontend.user.profile', compact('user'));
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
            'profile' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only('name', 'email', 'phone', 'address');

            if ($request->hasFile('profile')) {
                $data['profile'] = FileUploadHelper::replace(
                    $request->file('profile'),
                    $user->profile,
                    'customers/profiles'
                );
            }

            $user->update($data);

            DB::commit();
            return back()->with('success', 'Your profile has been updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['profile'])) {
                FileUploadHelper::delete($data['profile']);
            }

            return back()->with('error', 'There was a problem updating your profile: ' . $e->getMessage());
        }
    }

    // update your password
    public function updatePassword(Request $request)
    {

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'New password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $user = Auth::guard('customer')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with([
                'error' => 'current password is incorrect.',
                'active_tab' => 'password'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with([
            'success' => 'Password updated successfully.',
            'active_tab' => 'password'
        ]);
    }
    public function dashboard()
    {
        $user = Auth::guard('customer')->user();

        $allOrders = $user->orders()->with('orderDetails.product')->latest()->take(15)->get();
        $recentOrders = $allOrders->take(5);

        $wishlistItems = Wishlist::where('customer_id', $user->id)
            ->with(['product.variations', 'product.brand'])
            ->latest()
            ->get();

        $paymentMethods = CustomerPaymentMethod::where('status', Status::Active->value)
            ->get();

        $totalOrders = $allOrders->count();
        $totalSpent = $allOrders->where('status', 'delivered')->sum('grand_total');
        $wishlistCount = $wishlistItems->count();

        return  $this->view('frontend.user.dashboard', compact(
            'user',
            'totalOrders',
            'totalSpent',
            'wishlistCount',
            'recentOrders',
            'allOrders',
            'wishlistItems',
            'paymentMethods',

        ));
    }
}

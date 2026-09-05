@extends('saas.partner.layout')

@section('title', 'Verify Partner Email')

@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold border border-brand-100">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Verify Your Email</h2>
        <p class="text-sm text-slate-500 mt-1">
            We sent a 6-digit verification code to <br>
            <span class="font-bold text-slate-800">{{ $email }}</span>
        </p>
    </div>

    <form action="{{ route('partner.register.verify-submit') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                6-Digit Verification Code *
            </label>
            <input type="text" name="otp" required autofocus maxlength="6" pattern="[0-9]{6}"
                class="w-full text-center tracking-[0.4em] text-2xl font-mono font-bold py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-slate-800"
                placeholder="123456">
            @error('otp')
                <p class="text-xs text-rose-500 mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition duration-150">
            Verify & Create Partner Account
        </button>
    </form>

    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
        <form action="{{ route('partner.register.resend-otp') }}" method="POST" class="inline">
            @csrf
            <span>Didn't receive the code? </span>
            <button type="submit" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                Resend Code
            </button>
        </form>
        <a href="{{ route('partner.register') }}" class="text-slate-500 hover:text-slate-700 hover:underline">
            Edit Details
        </a>
    </div>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-600">
        Already have a partner account? 
        <a href="{{ route('partner.login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Sign In here</a>
    </div>
</div>
@endsection

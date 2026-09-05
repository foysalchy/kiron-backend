@extends('saas.partner.layout')

@section('title', 'Partner Login')

@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold border border-brand-100">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Partner Portal Login</h2>
        <p class="text-sm text-slate-500 mt-1">Sign in to track your referrals, earnings & payouts</p>
    </div>

    <form action="{{ route('partner.login.submit') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                placeholder="partner@example.com">
        </div>

        <div>
            <div class="flex justify-between items-center mb-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
            </div>
            <input type="password" name="password" required
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                placeholder="••••••••">
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition duration-150">
            Sign In to Partner Portal
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-600">
        Don't have a partner account? 
        <a href="{{ route('partner.register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Apply to become a Partner</a>
    </div>
</div>
@endsection

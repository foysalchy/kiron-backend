@extends('saas.partner.layout')

@section('title', 'Join Partner Program')

@section('content')
<div class="max-w-xl mx-auto my-6 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold border border-brand-100">
            <i class="fa-solid fa-users-viewfinder"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Become a Dorja Partner</h2>
        <p class="text-sm text-slate-500 mt-1">Earn recurring commissions by referring businesses to Dorja ERP & POS</p>
    </div>

    <form action="{{ route('partner.register.submit') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Full Name / Organization *</label>
            <input type="text" name="name" value="{{ old('name', $pending['name'] ?? '') }}" required
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                placeholder="John Doe or Acme Consultants">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address (OTP will be sent) *</label>
                <input type="email" name="email" value="{{ old('email', $pending['email'] ?? '') }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                    placeholder="partner@example.com">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $pending['phone'] ?? '') }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                    placeholder="+880 1700 000000">
            </div>
        </div>

        @if($groups && count($groups) > 0)
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Partner Category / Group</label>
            <select name="referral_group_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm bg-white">
                @foreach($groups as $grp)
                    <option value="{{ $grp->id }}" {{ old('referral_group_id', $pending['referral_group_id'] ?? '') == $grp->id ? 'selected' : '' }}>
                        {{ $grp->name }} ({{ $grp->default_commission_rate }}% base commission + Buyer gets {{ $grp->buyer_discount_rate ?? 0 }}% signup discount)
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Password *</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                    placeholder="Min 6 characters">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                    placeholder="Repeat password">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition duration-150 flex items-center justify-center gap-2">
                <span>Continue & Verify Email</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-600">
        Already have a partner account? 
        <a href="{{ route('partner.login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Sign In here</a>
    </div>
</div>
@endsection

@extends('saas.partner.layout')

@section('title', 'Partner Profile & Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Partner Profile & Payout Settings</h1>
        <p class="text-sm text-slate-500">Manage your contact information, default payout methods and account password</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('partner.profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Partner / Org Name</label>
                        <input type="text" name="name" value="{{ old('name', $partner->name) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" value="{{ $partner->email }}" disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed">
                        <span class="text-[11px] text-slate-400">Email cannot be changed directly</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $partner->phone) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Referral Code</label>
                        <input type="text" value="{{ $partner->referral_code }}" disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-brand-600 font-mono font-bold text-sm cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Default Payout Configuration
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Default Payout Channel</label>
                        <select name="payout_method" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm bg-white">
                            <option value="">-- Select Channel --</option>
                            <option value="bkash" {{ old('payout_method', $partner->payout_method) === 'bkash' ? 'selected' : '' }}>bKash</option>
                            <option value="nagad" {{ old('payout_method', $partner->payout_method) === 'nagad' ? 'selected' : '' }}>Nagad</option>
                            <option value="rocket" {{ old('payout_method', $partner->payout_method) === 'rocket' ? 'selected' : '' }}>Rocket</option>
                            <option value="bank_transfer" {{ old('payout_method', $partner->payout_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Default Account Details</label>
                        <textarea name="payout_details" rows="2" placeholder="e.g. bKash: 01700000000 (Personal) or Bank: City Bank, A/C: 1234567890, Branch: Gulshan"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">{{ old('payout_details', $partner->payout_details) }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Security / Change Password
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep unchanged"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" placeholder="Repeat new password"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

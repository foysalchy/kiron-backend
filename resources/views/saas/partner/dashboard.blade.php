@extends('saas.partner.layout')

@section('title', 'Partner Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Hero / Referral Link Banner -->
    <div class="bg-gradient-to-r from-brand-600 via-indigo-600 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-500/10">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase text-brand-100 border border-white/10">
                    <span>{{ $partner->group ? $partner->group->name : 'Standard Partner' }}</span>
                    <span>•</span>
                    <span>{{ $stats['current_commission_rate'] }}% Current Commission</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, {{ $partner->name }}!</h1>
                <p class="text-indigo-100 text-sm max-w-xl">
                    Share your unique referral link or code with businesses. When they register and subscribe, you earn recurring commissions!
                </p>
            </div>

            <!-- Referral Link & Code Box -->
            <div class="w-full lg:w-auto bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-indigo-200 uppercase tracking-wider mb-1">Your Referral Link</label>
                    <div class="flex items-center bg-white rounded-xl p-1 shadow-inner">
                        <input type="text" id="refLink" readonly value="{{ $partner->referral_link }}" 
                            class="bg-transparent text-slate-800 text-xs sm:text-sm font-medium px-3 py-1.5 focus:outline-none w-full sm:w-80">
                        <button onclick="copyToClipboard('refLink', 'linkBtn')" id="linkBtn"
                            class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                            <i class="fa-regular fa-copy mr-1"></i> Copy Link
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs text-indigo-100">Referral Code: <strong class="text-white font-mono bg-white/20 px-2 py-0.5 rounded">{{ $partner->referral_code }}</strong></span>
                    @if($partner->group && $partner->group->buyer_discount_rate > 0)
                        <span class="text-xs text-emerald-300 font-semibold bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-500/30">
                            🎉 Your referrals get {{ $partner->group->buyer_discount_rate }}% discount!
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tier & Milestone Progress Bar -->
        @if(isset($stats['tier_info']['next_tier']))
        <div class="mt-6 pt-6 border-t border-white/15">
            <div class="flex justify-between items-center text-xs font-medium text-indigo-100 mb-2">
                <span>Current Tier: <strong class="text-white">{{ $stats['tier_info']['current_tier_name'] }} ({{ $stats['current_commission_rate'] }}%)</strong></span>
                <span>Next Milestone: <strong class="text-amber-300">{{ $stats['tier_info']['next_tier']['name'] }} ({{ $stats['tier_info']['next_tier']['commission_rate'] }}%)</strong> ({{ $stats['tier_info']['sales_needed'] }} more sales needed)</span>
            </div>
            <div class="w-full bg-black/25 rounded-full h-2.5 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-400 to-amber-300 h-2.5 rounded-full transition-all duration-500" 
                     style="width: {{ min(100, max(5, ($partner->total_sales_count / max(1, $stats['tier_info']['next_tier']['min_sales'])) * 100)) }}%"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Referrals -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Referred</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-slate-900">{{ $stats['total_referrals'] }}</div>
                <div class="flex items-center space-x-2 mt-2 text-xs">
                    <span class="text-emerald-600 font-semibold flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span> {{ $stats['active_referrals'] }} Active
                    </span>
                    <span class="text-slate-400">•</span>
                    <span class="text-slate-500 font-medium">
                        {{ $stats['inactive_referrals'] }} Inactive
                    </span>
                </div>
            </div>
        </div>

        <!-- Paid Subscriptions / Sales -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Converted Sales</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-slate-900">{{ $partner->total_sales_count }}</div>
                <div class="text-xs text-slate-500 mt-2">
                    Current rate: <strong class="text-indigo-600 font-bold">{{ $stats['current_commission_rate'] }}%</strong>
                </div>
            </div>
        </div>

        <!-- Wallet Balance -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Balance</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-emerald-600">৳{{ number_format($partner->wallet_balance, 2) }}</div>
                <div class="mt-2">
                    <a href="{{ route('partner.withdrawals') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center">
                        Request Payout <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Lifetime Earnings -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lifetime Earned</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-slate-900">৳{{ number_format($partner->total_earned, 2) }}</div>
                <div class="text-xs text-slate-500 mt-2">
                    Paid out: ৳{{ number_format($partner->total_withdrawn, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Referred Companies -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-sm">Recent Referred Companies</h3>
                <a href="{{ route('partner.referrals') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAttributions as $attr)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                            {{ substr($attr->company->name ?? 'C', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 text-sm">{{ $attr->company->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">Joined {{ $attr->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($attr->company && $attr->company->status == 1) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ ($attr->company && $attr->company->status == 1) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300"></i>
                    <p>No referred companies yet.</p>
                    <p class="text-xs text-slate-500 mt-1">Share your link to get your first referral!</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Commission Earnings -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-sm">Recent Earnings & Commissions</h3>
                <a href="{{ route('partner.earnings') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentCommissions as $comm)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                    <div>
                        <div class="font-semibold text-slate-900 text-sm">{{ $comm->company->name ?? 'Referred Business' }}</div>
                        <div class="text-xs text-slate-500">
                            Invoice: ৳{{ number_format($comm->invoice_amount, 2) }} ({{ $comm->commission_rate }}% rate) • {{ $comm->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-emerald-600 text-sm">+৳{{ number_format($comm->commission_amount, 2) }}</div>
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">
                            {{ $comm->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="fa-solid fa-coins text-3xl mb-2 text-slate-300"></i>
                    <p>No earnings recorded yet.</p>
                    <p class="text-xs text-slate-500 mt-1">Earnings will appear when referred businesses subscribe.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function copyToClipboard(elementId, btnId) {
    const copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);

    const btn = document.getElementById(btnId);
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Copied!';
    btn.classList.add('bg-emerald-600');
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('bg-emerald-600');
    }, 2000);
}
</script>
@endpush
@endsection

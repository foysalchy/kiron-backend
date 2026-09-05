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
                    <span class="text-amber-300 font-bold">{{ $stats['tier_info']['current_tier_name'] }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, {{ $partner->name }}!</h1>
                <p class="text-indigo-100 text-sm max-w-xl">
                    Share your unique referral link or code. When referred businesses subscribe, your commissions automatically increase as you hit higher sales milestone tiers!
                </p>
            </div>

            <!-- Referral Link & Code Box -->
            <div class="w-full lg:w-auto bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-indigo-200 uppercase tracking-wider mb-1">Your Unique Referral Link</label>
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
                            🎉 Referrals get {{ $partner->group->buyer_discount_rate }}% discount!
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tier & Milestone Progress Bar -->
        @if(!empty($stats['tier_info']['next_tier']))
        <div class="mt-6 pt-6 border-t border-white/15">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-xs font-medium text-indigo-100 mb-2 gap-1">
                <span>
                    Current Rank: <strong class="text-white font-bold">{{ $stats['tier_info']['current_tier_name'] }}</strong> ({{ $stats['current_commission_rate'] }}% Commission)
                </span>
                <span class="text-amber-200 font-semibold flex items-center gap-1">
                    <span>🚀 Next Rank: <strong class="text-white">{{ $stats['tier_info']['next_tier']['name'] }}</strong> ({{ $stats['tier_info']['next_tier']['rate'] }}% Commission)</span>
                    <span class="bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full border border-amber-300/30">
                        {{ $stats['tier_info']['sales_needed'] }} more sales needed
                    </span>
                </span>
            </div>
            <div class="w-full bg-black/25 rounded-full h-3 overflow-hidden p-0.5">
                <div class="bg-gradient-to-r from-emerald-400 via-amber-300 to-amber-400 h-2 rounded-full transition-all duration-500" 
                     style="width: {{ min(100, max(8, ($partner->total_sales_count / max(1, $stats['tier_info']['next_tier']['min_sales'])) * 100)) }}%"></div>
            </div>
            <div class="flex justify-between text-[11px] text-indigo-200 mt-1">
                <span>{{ $partner->total_sales_count }} sales completed</span>
                <span>Goal: {{ $stats['tier_info']['next_tier']['min_sales'] }} sales</span>
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

    <!-- Tier Milestone & Rank Roadmap Ladder Section -->
    @if(!empty($stats['tier_info']['all_tiers']) && count($stats['tier_info']['all_tiers']) > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-500 text-lg"></i>
                    Commission Ranks & Sales Milestone Roadmap
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Your commission percentage automatically unlocks higher rates as your total referred subscription sales grow!
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <div class="text-xs bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-xl border border-emerald-200 font-semibold flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-tag text-emerald-600"></i>
                    Customer Signup Discount: <strong class="font-extrabold text-emerald-700 text-sm">{{ $partner->group ? ($partner->group->buyer_discount_rate ?? 0) : 0 }}% OFF</strong>
                </div>
                <div class="text-xs bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 font-medium">
                    Total Sales Made: <strong class="text-brand-600 font-bold">{{ $partner->total_sales_count }}</strong>
                </div>
            </div>
        </div>

        <!-- Customer Benefit Callout Banner -->
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border border-emerald-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shrink-0 shadow-md shadow-emerald-600/20 mt-0.5 sm:mt-0">
                    <i class="fa-solid fa-gift text-lg"></i>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2 flex-wrap">
                        <span>Customer Join Benefit:</span>
                        <span class="bg-emerald-600 text-white text-[11px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                            {{ $partner->group ? ($partner->group->buyer_discount_rate ?? 0) : 0 }}% Instant Discount
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Any customer or business signing up using your code <strong class="font-mono text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded font-bold">{{ $partner->referral_code }}</strong> or link will automatically receive a <strong>{{ $partner->group ? ($partner->group->buyer_discount_rate ?? 0) : 0 }}% discount</strong> on their subscription packages.
                    </p>
                </div>
            </div>
            <button onclick="copyToClipboard('refLink', 'linkBtn2')" id="linkBtn2"
                class="shrink-0 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-regular fa-copy"></i> Copy Referral Link
            </button>
        </div>

        <!-- Tier Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-{{ min(4, count($stats['tier_info']['all_tiers'])) }} gap-4">
            @foreach($stats['tier_info']['all_tiers'] as $tier)
            <div class="rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between {{ $tier['is_current'] ? 'bg-gradient-to-b from-brand-50/70 to-indigo-50/40 border-brand-500 shadow-md ring-2 ring-brand-500/20' : ($tier['is_unlocked'] ? 'bg-emerald-50/30 border-emerald-200' : 'bg-slate-50/70 border-slate-200 opacity-90') }}">
                <div>
                    <div class="flex justify-between items-start">
                        <span class="text-xs font-bold uppercase tracking-wider {{ $tier['is_current'] ? 'text-brand-700' : ($tier['is_unlocked'] ? 'text-emerald-700' : 'text-slate-500') }}">
                            {{ $tier['name'] }}
                        </span>
                        @if($tier['is_current'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-600 text-white shadow-sm">
                                ★ ACTIVE RANK
                            </span>
                        @elseif($tier['is_unlocked'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-check mr-1 text-[8px]"></i> Achieved
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-600">
                                <i class="fa-solid fa-lock mr-1 text-[8px]"></i> Locked
                            </span>
                        @endif
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900">
                            {{ $tier['commission_rate'] }}%
                            <span class="text-xs font-medium text-slate-500">partner commission</span>
                        </div>
                        <div class="text-xs text-slate-600 mt-1 font-medium">
                            <i class="fa-solid fa-bullseye text-slate-400 mr-1"></i>
                            Sales Target: <strong>{{ $tier['min_sales'] }} – {{ $tier['max_sales'] ? $tier['max_sales'] : '∞' }} sales</strong>
                        </div>
                        <div class="mt-2 text-[11px] text-emerald-700 bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-100 flex items-center gap-1 font-semibold">
                            <i class="fa-solid fa-tag text-emerald-500 text-[10px]"></i>
                            Customer gets: {{ $partner->group ? ($partner->group->buyer_discount_rate ?? 0) : 0 }}% signup discount
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t {{ $tier['is_current'] ? 'border-brand-200' : 'border-slate-200' }} text-xs">
                    @if($tier['is_current'])
                        <div class="text-brand-700 font-bold flex items-center">
                            <i class="fa-solid fa-circle-dot mr-1.5 text-brand-600 animate-pulse"></i>
                            Currently Earning at this rate
                        </div>
                    @elseif($tier['is_unlocked'])
                        <div class="text-emerald-600 font-semibold flex items-center">
                            <i class="fa-solid fa-circle-check mr-1.5"></i>
                            Milestone completed
                        </div>
                    @else
                        <div class="text-slate-500">
                            <strong class="text-amber-600 font-bold">{{ $tier['sales_needed'] }}</strong> more sales needed to upgrade
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                            Invoice: ৳{{ number_format($comm->invoice_amount ?? $comm->sale_amount ?? 0, 2) }} ({{ $comm->commission_rate }}% rate) • {{ $comm->created_at->format('M d, Y') }}
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

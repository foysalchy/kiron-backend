@extends('saas.partner.layout')

@section('title', 'Referred Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Referred Accounts</h1>
            <p class="text-sm text-slate-500">List of businesses and companies registered using your referral code/link</p>
        </div>
        <div class="flex items-center space-x-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-xs text-slate-600">
            <span>Your Code: <strong class="text-brand-600 font-mono">{{ $partner->referral_code }}</strong></span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Company Name</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Registration Date</th>
                        <th class="px-6 py-4">First Subscription</th>
                        <th class="px-6 py-4 text-right">Commissions Generated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($referrals as $index => $attr)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-slate-400">
                            {{ $referrals->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $attr->company->name ?? 'Company #'.$attr->company_id }}</div>
                            <div class="text-xs text-slate-400">{{ $attr->company->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($attr->company && $attr->company->status == 1)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span> Inactive / Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $attr->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($attr->first_subscribed_at)
                                <span class="text-emerald-600 font-semibold">{{ \Carbon\Carbon::parse($attr->first_subscribed_at)->format('M d, Y') }}</span>
                            @else
                                <span class="text-slate-400 italic">Trial / Not Yet</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">
                            ৳{{ number_format($attr->commissions->sum('commission_amount'), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-users text-4xl mb-3 text-slate-300"></i>
                            <p class="font-medium text-slate-600">No referred accounts yet.</p>
                            <p class="text-xs text-slate-400 mt-1">When businesses register with your referral link or code, they will show up here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $referrals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

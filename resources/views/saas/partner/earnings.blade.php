@extends('saas.partner.layout')

@section('title', 'Commissions & Earnings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Commission Earnings</h1>
            <p class="text-sm text-slate-500">Detailed breakdown of commissions earned from referred customer subscription payments</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs text-slate-500">Total Earned:</span>
                <span class="font-extrabold text-slate-900 ml-1">৳{{ number_format($partner->total_earned, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Earnings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Referred Company</th>
                        <th class="px-6 py-4">Invoice Amount</th>
                        <th class="px-6 py-4">Applied Rate</th>
                        <th class="px-6 py-4">Commission</th>
                        <th class="px-6 py-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($commissions as $index => $comm)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-slate-400">
                            {{ $commissions->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-700">
                            {{ $comm->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">
                            {{ $comm->company->name ?? 'Company #'.$comm->company_id }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700">
                            ৳{{ number_format($comm->invoice_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-100">
                                {{ $comm->commission_rate }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-emerald-600 text-base">
                            +৳{{ number_format($comm->commission_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $comm->status === 'credited' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $comm->status === 'credited' ? 'bg-emerald-500' : 'bg-slate-400' }} mr-1.5"></span>
                                {{ ucfirst($comm->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-4xl mb-3 text-slate-300"></i>
                            <p class="font-medium text-slate-600">No commissions earned yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Commissions are automatically added when your referred accounts pay for subscriptions.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

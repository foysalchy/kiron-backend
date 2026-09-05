@extends('saas.partner.layout')

@section('title', 'Payouts & Withdrawals')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Payouts & Withdrawals</h1>
            <p class="text-sm text-slate-500">Request payout of your available referral commission balance</p>
        </div>
        <div>
            <button onclick="document.getElementById('payoutModal').classList.remove('hidden')"
                class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
                <i class="fa-solid fa-money-bill-transfer mr-2"></i> Request Payout
            </button>
        </div>
    </div>

    <!-- Balance Summary Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-gradient-to-tr from-emerald-600 to-teal-500 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/10">
            <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Available Balance</span>
            <div class="text-3xl font-extrabold mt-2">৳{{ number_format($partner->wallet_balance, 2) }}</div>
            <p class="text-xs text-emerald-100 mt-2">Ready for withdrawal (Min ৳500.00)</p>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Withdrawn</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">৳{{ number_format($partner->total_withdrawn, 2) }}</div>
            <p class="text-xs text-slate-500 mt-2">Successfully transferred to your account</p>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Saved Payout Method</span>
            <div class="text-lg font-bold text-slate-800 mt-2 uppercase">{{ $partner->payout_method ?: 'Not Configured' }}</div>
            <p class="text-xs text-slate-500 mt-1 truncate">{{ $partner->payout_details ?: 'Update in your profile settings' }}</p>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-sm">Withdrawal History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Req #</th>
                        <th class="px-6 py-4">Date Requested</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Method & Account</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Transaction Ref / Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($withdrawals as $w)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-slate-800">
                            #REQ-{{ str_pad($w->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-slate-700">
                            {{ $w->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 text-base">
                            ৳{{ number_format($w->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800 text-xs uppercase">{{ $w->payment_method }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $w->account_details }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($w->status === 'approved' || $w->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> {{ ucfirst($w->status) }}
                                </span>
                            @elseif($w->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span> Pending Review
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($w->transaction_reference)
                                <div><strong class="text-slate-700">Txn ID:</strong> <span class="font-mono text-emerald-600 font-semibold">{{ $w->transaction_reference }}</span></div>
                            @endif
                            @if($w->admin_notes)
                                <div class="text-slate-500 italic mt-0.5">{{ $w->admin_notes }}</div>
                            @endif
                            @if(!$w->transaction_reference && !$w->admin_notes)
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-money-bill-transfer text-4xl mb-3 text-slate-300"></i>
                            <p class="font-medium text-slate-600">No withdrawal requests yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Submit your first payout request once you have minimum ৳500 in earnings.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Request Payout -->
<div id="payoutModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Request Payout</h3>
                <p class="text-xs text-slate-500">Balance available: <strong class="text-emerald-600">৳{{ number_format($partner->wallet_balance, 2) }}</strong></p>
            </div>
            <button onclick="document.getElementById('payoutModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('partner.withdrawals.request') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Withdrawal Amount (BDT) *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold">৳</span>
                    <input type="number" step="0.01" min="500" max="{{ $partner->wallet_balance }}" name="amount" required
                        value="{{ min($partner->wallet_balance, 500) }}"
                        class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm font-bold text-slate-800">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Minimum payout amount is ৳500.00</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Payout Method *</label>
                <select name="payment_method" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm bg-white">
                    <option value="bkash" {{ $partner->payout_method === 'bkash' ? 'selected' : '' }}>bKash (Personal/Merchant)</option>
                    <option value="nagad" {{ $partner->payout_method === 'nagad' ? 'selected' : '' }}>Nagad</option>
                    <option value="rocket" {{ $partner->payout_method === 'rocket' ? 'selected' : '' }}>Rocket</option>
                    <option value="bank_transfer" {{ $partner->payout_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer (BEFTN/NPSB)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Account Details / Number *</label>
                <textarea name="account_details" rows="2" required placeholder="e.g. bKash: 01700000000 (Personal) or Bank Name, A/C No, Routing No"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">{{ $partner->payout_details }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Additional Note (Optional)</label>
                <input type="text" name="note" placeholder="Any instruction for the admin"
                    class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm">
            </div>

            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('payoutModal').classList.add('hidden')"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

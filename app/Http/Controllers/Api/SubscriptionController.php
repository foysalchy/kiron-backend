<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubscriptionController extends Controller
{
    public function applyDiscount(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'discount_amount' => 'required|numeric|min:0',
            'note'            => 'nullable|string|max:255',
        ]);

        $sub = CompanySubscription::findOrFail($id);
        $sub->update([
            'discount_amount' => $data['discount_amount'],
            'discount_note'   => $data['note'] ?? null,
        ]);

        return response()->json(['message' => 'Discount applied.']);
    }
    public function manualPayments(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'discount_amount' => 'required|numeric|min:0',
            'note'            => 'nullable|string|max:255',
        ]);

        $sub = CompanySubscription::findOrFail($id);
        $sub->update([
            'discount_amount' => $data['discount_amount'],
            'discount_note'   => $data['note'] ?? null,
            'amount_paid'     => max(0, $sub->amount_paid - $data['discount_amount']),
        ]);

        return response()->json(['message' => 'Discount applied.']);
    }
    public function addPayment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'account_holder_name' => 'nullable|string|max:255',
            'payment_method'      => 'required|string|max:255',
            'number'              => 'nullable|string|max:50',
            'transaction_id'      => 'nullable|string|max:255|unique:subscription_payments,transaction_id',
            'document'            => 'required|image|mimes:jpg,jpeg,png,pdf,webp|max:5120',
            'pricing_package_id'  => 'nullable|exists:pricing_packages,id', // নতুন
            'billing_cycle'       => 'nullable|in:monthly,quarterly,yearly', // নতুন
        ]);

        DB::transaction(function () use ($request, $id) {
            $subscription = CompanySubscription::findOrFail($id);

            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = FileUploadHelper::uploadImage(
                    $request->file('document'),
                    'payment_documents'
                );
            }

            DB::table('subscription_payments')->insert([
                'subscription_id'    => $subscription->id,
                'company_id'         => $subscription->company_id,
                'payment_method'     => $request->payment_method,
                'amount'             => $subscription->amount_paid,
                'transaction_id'     => $request->transaction_id ?? null,
                'sender_number'      => $request->number ?? null,
                'account_number'     => null,
                'bank_name'          => null,
                'status'             => 'pending',
                'meta'               => json_encode([
                    'account_holder_name' => $request->account_holder_name ?? null,
                    'document_path'       => $documentPath,
                ]),
                'paid_at'            => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $updateData = [
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
            ];

            // upgrade package চাইলে সাথে সাথে active করুন
            if ($request->pricing_package_id) {
                $updateData['pricing_package_id'] = $request->pricing_package_id;
                $updateData['billing_cycle']       = $request->billing_cycle;
            }

            $subscription->update($updateData);
        });

        return response()->json(['message' => 'Payment submitted successfully.']);
    }

    public function upgradePayment(Request $request): JsonResponse
    {
        $request->validate([
            'account_holder_name' => 'nullable|string|max:255',
            'payment_method'      => 'required|string|max:255',
            'number'              => 'nullable|string|max:50',
            'transaction_id'      => 'nullable|string|max:255|unique:subscription_payments,transaction_id',
            'document'            => 'required|image|mimes:jpg,jpeg,png,pdf,webp|max:5120',
            'pricing_package_id'  => 'required|exists:pricing_packages,id',
            'billing_cycle'       => 'required|in:monthly,quarterly,yearly',
        ]);

        DB::transaction(function () use ($request) {
            $subscription = CompanySubscription::where('company_id', auth()->user()->company_id)
                ->latest()
                ->firstOrFail();

            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = FileUploadHelper::uploadImage(
                    $request->file('document'),
                    'payment_documents'
                );
            }

            DB::table('subscription_payments')->insert([
                'subscription_id' => $subscription->id,
                'company_id'      => $subscription->company_id,
                'payment_method'  => $request->payment_method,
                'amount'          => $subscription->amount_paid,
                'transaction_id'  => $request->transaction_id ?? null,
                'sender_number'   => $request->number ?? null,
                'account_number'  => null,
                'bank_name'       => null,
                'status'          => 'pending',
                'meta'            => json_encode([
                    'account_holder_name' => $request->account_holder_name ?? null,
                    'document_path'       => $documentPath,
                ]),
                'paid_at'         => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $subscription->update([
                'pricing_package_id' => $request->pricing_package_id,
                'billing_cycle'      => $request->billing_cycle,
                'payment_method'     => $request->payment_method,
                'payment_status'     => 'paid',
            ]);
        });

        return response()->json(['message' => 'Upgrade payment submitted successfully.']);
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:success,failed',
        ]);

        $payment = SubscriptionPayment::findOrFail($id);
        $sourceSubscription = $payment->subscription; 

        $payment->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'success') {

            if (! $sourceSubscription) {
                return response()->json([
                    'message' => 'Payment updated but no related subscription found to activate.',
                    'data'    => ['payment' => $payment->fresh()],
                ], 422);
            }

            $startsAt = Carbon::now();
            $endsAt   = $sourceSubscription->billing_cycle === 'yearly'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth();

            $newSubscription = CompanySubscription::create([
                'company_id'          => $sourceSubscription->company_id,
                'pricing_package_id'  => $sourceSubscription->pricing_package_id,
                'billing_cycle'       => $sourceSubscription->billing_cycle,
                'amount_paid'         => $payment->amount ?? $sourceSubscription->amount_paid,
                'currency'            => $sourceSubscription->currency,
                'payment_method'      => $payment->payment_method ?? $sourceSubscription->payment_method,
                'payment_status'      => 'paid',
                'discount_amount'     => $sourceSubscription->discount_amount,
                'discount_note'       => $sourceSubscription->discount_note,
                'transaction_id'      => $payment->transaction_id ?? $sourceSubscription->transaction_id,
                'trial_ends_at'       => null, // ekhon paid, ar trial na
                'starts_at'           => $startsAt,
                'ends_at'             => $endsAt,
                'status'              => Status::Active->value,
            ]);

            $payment->update(['subscription_id' => $newSubscription->id]);

            // Process Referral Commission for the successful payment
            try {
                app(\App\Services\ReferralService::class)->processSubscriptionCommission($newSubscription, $payment->amount);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Commission error on payment approval: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Payment marked successful and subscription activated.',
                'data'    => [
                    'payment'      => $payment->fresh(),
                    'subscription' => $newSubscription,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Payment marked as failed.',
            'data'    => ['payment' => $payment->fresh()],
        ]);
    }
public function billing(Request $request): JsonResponse
{
    // ── Existing companies ──
    $companyQuery = Company::with([
        'currentSubscription.pricingPackage',
        'subscriptions.subscriptionPayments',
        'subscriptions' => fn($q) => $q->with(['pricingPackage'])->latest(),
        'referralAttribution.referralPartner.group'
    ]);

    if ($request->search) {
        $companyQuery->where(
            fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
        );
    }

    if ($request->status && $request->status !== 'all') {
        $status = $request->status === 'failed' ? ['failed', 'cancelled'] : [$request->status];
        $companyQuery->whereHas('currentSubscription', fn($q) => $q->whereIn('payment_status', $status));
    }

    if ($request->date_from) {
        $companyQuery->whereHas('currentSubscription', fn($q) => $q->whereDate('starts_at', '>=', $request->date_from));
    }
    if ($request->date_to) {
        $companyQuery->whereHas('currentSubscription', fn($q) => $q->whereDate('starts_at', '<=', $request->date_to));
    }

    $companies = $companyQuery->latest()->get();

    // Extra charges attach (existing companies)
    foreach ($companies as $company) {
        foreach ($company->subscriptions as $sub) {
            $startMonth = \Carbon\Carbon::parse($sub->starts_at)->format('Y-m');
            $endMonth   = \Carbon\Carbon::parse($sub->ends_at)->format('Y-m');
            $sub->setRelation(
                'extra_order_charges',
                \App\Models\ExtraOrderCharge::with('order:id,order_no')
                    ->where('company_id', $company->id)
                    ->whereBetween('month', [$startMonth, $endMonth])
                    ->get()
            );
        }
    }

    // ── Orphan subscriptions (company force-deleted) ──
    $orphanQuery = \App\Models\CompanySubscription::with(['pricingPackage', 'subscriptionPayments'])
        ->whereDoesntHave('company');

    if ($request->status && $request->status !== 'all') {
        $status = $request->status === 'failed' ? ['failed', 'cancelled'] : [$request->status];
        $orphanQuery->whereIn('payment_status', $status);
    }
    if ($request->date_from) {
        $orphanQuery->whereDate('starts_at', '>=', $request->date_from);
    }
    if ($request->date_to) {
        $orphanQuery->whereDate('starts_at', '<=', $request->date_to);
    }

    $orphanSubs = $orphanQuery->latest()->get();

    // company_id ধরে group করা — একই company id এর subscription গুলো একসাথে
$deletedCompanies = $orphanSubs->groupBy('company_id')->map(function ($subs, $companyId) {
    foreach ($subs as $sub) {
        $sub->setRelation('extra_order_charges', collect());
    }

    $latestSub = $subs->sortByDesc('starts_at')->first();

    return [
        'id'                   => 'deleted-' . $companyId,
        'name'                 => $latestSub->company_name_snapshot ?? 'Deleted Company',
        'email'                => $latestSub->company_email_snapshot,
        'deleted'              => true,
        'original_company_id'  => $companyId,
        'current_subscription' => $latestSub,
        'subscriptions'        => $subs->values(),
    ];
})->values();

    // Search filter orphan এ apply (নাম না থাকায় শুধু original_company_id দিয়ে সার্চ, চাইলে বাদও দিতে পারেন)
    if ($request->search) {
        $deletedCompanies = collect(); // deleted company তে searchable নাম নেই, তাই search চললে বাদ
    }

    // Company object বানানো (Eloquent collection এর সাথে merge করার জন্য array হিসেবে পাঠাচ্ছি)
    $allCompanies = $companies->toArray();
    foreach ($deletedCompanies as $dc) {
        $allCompanies[] = $dc;
    }

    // ── Stats — full dataset ──
    $allSubs = \App\Models\CompanySubscription::query();

    $stats = [
        'total_companies'   => Company::count(),
        'total_paid_amount' => (clone $allSubs)->where('payment_status', 'paid')->sum('amount_paid'),
        'pending_count'     => (clone $allSubs)->where('payment_status', 'pending')->count(),
        'pending_amount'    => (clone $allSubs)->where('payment_status', 'pending')->sum('amount_paid'),
        'unpaid_count'      => (clone $allSubs)->whereIn('payment_status', ['failed', 'cancelled'])->count(),
        'unpaid_amount'     => (clone $allSubs)->whereIn('payment_status', ['failed', 'cancelled'])->sum('amount_paid'),
    ];

    return response()->json([
        'data'  => $allCompanies,
        'stats' => $stats,
    ]);
}
}

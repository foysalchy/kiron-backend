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
        'referralAttribution.partner.group'
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

    // ── NEW: Package upgrade requests, grouped by company ──
    // Loaded without global scopes so soft-deleted / restricted companies still show their requests.
    $upgradeRequests = \App\Models\UpdgradePackageRequest::withoutGlobalScopes()
        ->with(['pricingPackage']) // target package being requested
        ->get();

    $upgradesByCompany = $upgradeRequests->groupBy('company_id');

    foreach ($companies as $company) {
        $company->setRelation(
            'upgrade_requests',
            $upgradesByCompany->get($company->id, collect())->values()
        );
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
    $deletedCompanies = $orphanSubs->groupBy('company_id')->map(function ($subs, $companyId) use ($upgradesByCompany) {
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
            // NEW: attach any upgrade requests that belonged to this now-deleted company
            'upgrade_requests'     => $upgradesByCompany->get($companyId, collect())->values(),
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
        // NEW: surfaced so the frontend can badge pending upgrade requests the same way it badges pending payments
        'pending_upgrades_count' => \App\Models\UpdgradePackageRequest::withoutGlobalScopes()
            ->where('status', \App\Enums\Status::Pending ?? 0)
            ->count(),
    ];

    return response()->json([
        'data'  => $allCompanies,
        'stats' => $stats,
    ]);
}

    public function unifiedBillingList(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $statusFilter = $request->query('status'); // all, paid, pending, failed
        $packageId = $request->query('pricing_package_id');

        // Fetch regular subscriptions & approved upgrades
        $subsQuery = CompanySubscription::with([
            'company.pricingPackage',
            'pricingPackage',
            'upgradeRequest.pricingPackage',
            'subscriptionPayments'
        ]);

        if ($search) {
            $subsQuery->whereHas('company', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($packageId) {
            $subsQuery->where('pricing_package_id', $packageId);
        }
        if ($statusFilter && $statusFilter !== 'all') {
            $mappedStatus = $statusFilter === 'failed' ? ['failed', 'cancelled'] : [$statusFilter];
            $subsQuery->whereIn('payment_status', $mappedStatus);
        }

        $subs = $subsQuery->get()->map(function ($s) {
            return [
                'entry_type'      => 'subscription',
                'id'              => $s->id,
                'company'         => $s->company,
                'current_package' => $s->upgrade_request_id ? ($s->company->pricingPackage ?? null) : $s->pricingPackage,
                'target_package'  => $s->upgrade_request_id ? ($s->upgradeRequest->pricingPackage ?? null) : null,
                'is_upgrade'      => $s->upgrade_request_id !== null,
                'billing_cycle'   => $s->billing_cycle,
                'amount_paid'     => $s->amount_paid,
                'payment_method'  => $s->payment_method,
                'payment_status'  => $s->payment_status,
                'transaction_id'  => $s->transaction_id,
                'status'          => $s->status,
                'created_at'      => $s->created_at,
                // Extra fields for view modal if needed
                'document_path'       => $s->subscriptionPayments->last()->meta['document_path'] ?? null,
                'account_holder_name' => $s->subscriptionPayments->last()->meta['account_holder_name'] ?? null,
                'sender_number'       => $s->subscriptionPayments->last()->sender_number ?? null,
            ];
        });

        // Fetch pending upgrades
        $pendingUpgradesQuery = \App\Models\UpdgradePackageRequest::with([
            'company.pricingPackage',
            'pricingPackage'
        ])->where('status', Status::Pending->value);

        if ($search) {
            $pendingUpgradesQuery->whereHas('company', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($packageId) {
            $pendingUpgradesQuery->where('pricing_package_id', $packageId);
        }
        if ($statusFilter && $statusFilter !== 'all') {
            // Pending upgrades are inherently "pending"
            if ($statusFilter !== 'pending') {
                $pendingUpgradesQuery->where('id', -1); // Force empty if looking for paid/failed
            }
        }

        $upgrades = $pendingUpgradesQuery->get()->map(function ($u) {
            return [
                'entry_type'      => 'upgrade_request',
                'id'              => $u->id,
                'company'         => $u->company,
                'current_package' => $u->company->pricingPackage ?? null,
                'target_package'  => $u->pricingPackage,
                'is_upgrade'      => true,
                'billing_cycle'   => $u->billing_cycle,
                'amount_paid'     => $u->amount_paid,
                'payment_method'  => $u->payment_method,
                'payment_status'  => 'pending',
                'transaction_id'  => $u->transaction_id,
                'status'          => $u->status,
                'created_at'      => $u->created_at,
                'document_path'       => $u->document_path,
                'account_holder_name' => $u->account_holder_name,
                'sender_number'       => $u->number,
            ];
        });

        // Merge and Sort
        $combined = $subs->concat($upgrades)->sortByDesc('created_at')->values();

        // Paginate manually
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = $request->query('per_page', 15);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $combined->forPage($page, $perPage)->values(),
            $combined->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Stats
        $allSubs = CompanySubscription::query();
        $stats = [
            'total_companies'   => Company::count(),
            'total_paid_amount' => (clone $allSubs)->where('payment_status', 'paid')->sum('amount_paid'),
            'pending_count'     => (clone $allSubs)->where('payment_status', 'pending')->count() + \App\Models\UpdgradePackageRequest::where('status', Status::Pending->value)->count(),
            'pending_amount'    => (clone $allSubs)->where('payment_status', 'pending')->sum('amount_paid') + \App\Models\UpdgradePackageRequest::where('status', Status::Pending->value)->sum('amount_paid'),
            'unpaid_count'      => (clone $allSubs)->whereIn('payment_status', ['failed', 'cancelled'])->count(),
            'unpaid_amount'     => (clone $allSubs)->whereIn('payment_status', ['failed', 'cancelled'])->sum('amount_paid'),
        ];

        return response()->json([
            'data'  => $paginated,
            'stats' => $stats,
        ]);
    }
}

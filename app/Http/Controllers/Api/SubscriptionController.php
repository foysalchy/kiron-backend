<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySubscription;
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
            'amount_paid'     => max(0, $sub->amount_paid - $data['discount_amount']),
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
            'number'              => 'nullable|string|max:50',
            'transaction_id'      => 'nullable|string|max:255',
            'document'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $id) {
            $subscription = CompanySubscription::findOrFail($id);

            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('payment_documents', 'public');
            }

            DB::table('subscription_payments')->insert([
                'subscription_id' => $subscription->id,
                'company_id'      => $subscription->company_id,
                'payment_method'  => 'manual',
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
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // subscription payment status pending এ রাখো — admin approve করলে paid হবে
            $subscription->update([
                'payment_method' => 'manual',
                'payment_status' => 'paid',
            ]);
        });

        return response()->json(['message' => 'Payment submitted successfully.']);
    }
    public function billing(Request $request): JsonResponse
    {
        $companies = Company::with([
            'currentSubscription.pricingPackage',
            'subscriptions' => fn($q) => $q->with(['pricingPackage'])->latest(),
        ])
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
            )
            ->latest()
            ->get();

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

        return response()->json(['data' => $companies]);
    }
}

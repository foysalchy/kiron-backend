<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\CompanySubscription;
use App\Models\PricingPackage;
use App\Models\UpdgradePackageRequest;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageUpgradeService
{
    /**
     * Fetch upgrade requests
     */
    public function getAllUpgradeRequests(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = $query = UpdgradePackageRequest::withoutGlobalScopes()->with(['company', 'pricingPackage']);


            if (!empty($filters['pricing_package_id'])) {
                $query->where('pricing_package_id', $filters['pricing_package_id']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching upgrade requests: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch requests');
        }
    }

    /**
     * Create an upgrade request (Handles upload internally using FileUploadHelper)
     */
    public function createUpgradeRequest(array $data): UpdgradePackageRequest
    {
        DB::beginTransaction();
        try {
            // Find package and relevant billing tier details
            $package = PricingPackage::with([
                'tiers' => fn($q) => $q->where('billing_cycle', $data['billing_cycle'])
            ])->findOrFail($data['pricing_package_id']);

            $tier = $package->tiers->first();
            if (!$tier) {
                throw ApiException::serverError("No tier found for the selected billing cycle.");
            }

            // Calculate paid amount
            $amountPaid = $tier->discount_price > 0 && $tier->discount_price < $tier->regular_price
                ? $tier->discount_price
                : $tier->regular_price;


            // Handle document upload using FileUploadHelper [pattern matching your Brand Service]
            if (isset($data['document'])) {
                $data['document_path'] = FileUploadHelper::uploadImage(
                    $data['document'],
                    'upgrades/documents'
                );
                // Remove file object key to avoid Model mass-assignment mismatch
                unset($data['document']);
            }

            // Set final structural attributes to persist
            $data['amount_paid']  = $amountPaid;
            $data['request_date'] = Carbon::now();
            $data['status']       = Status::Pending->value;

            $request = UpdgradePackageRequest::create($data);

            LogHelper::created(
                'upgrade_request',
                $request->id,
                $request->company_id,
                'Upgrade request generated for package ID: ' . $request->pricing_package_id
            );

            DB::commit();
            Log::info('Upgrade request successfully stored.', ['request_id' => $request->id]);

            return $request->load(['company', 'pricingPackage']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upgrade request creation failed: ' . $e->getMessage());
            throw ApiException::serverError($e->getMessage() ?: 'Failed to create upgrade request');
        }
    }

    /**
     * Status update method
     */
    public function updateStatus(int $id, int $status): UpdgradePackageRequest
    {
        DB::beginTransaction();
        try {
            $upgradeRequest = UpdgradePackageRequest::withoutGlobalScopes()->with(['company', 'pricingPackage'])->findOrFail($id);

            // Update status on the request itself
            $upgradeRequest->update([
                'status' => $status
            ]);

            // Execute subscription activations only if status becomes Approved
            if ($status === Status::Approved->value) {
                $company = $upgradeRequest->company;
                $package = $upgradeRequest->pricingPackage;

                if (!$company) {
                    throw ApiException::notFound('Company relation is missing from this request.');
                }
                if (!$package) {
                    throw ApiException::notFound('Pricing Package relation is missing from this request.');
                }

                $now = Carbon::now();
                $endsAt = match ($upgradeRequest->billing_cycle) {
                    'yearly'    => $now->copy()->addYear(),
                    'quarterly' => $now->copy()->addMonths(3),
                    default     => $now->copy()->addMonth(),
                };

                // Update previous subscription statuses
                $company->subscriptions()->update(['status' => Status::Inactive->value]);

                // Create the active company subscription record
                $subscription = CompanySubscription::create([
                    'company_id'         => $company->id,
                    'upgrade_request_id' => $upgradeRequest->id,
                    'pricing_package_id' => $package->id,
                    'billing_cycle'      => $upgradeRequest->billing_cycle,
                    'amount_paid'        => $upgradeRequest->amount_paid,
                    'payment_method'     => $upgradeRequest->payment_method,
                    'payment_status'     => 'paid',
                    'starts_at'          => $now,
                    'ends_at'            => $endsAt,
                    'status'             => Status::Active->value,
                ]);

                // Insert payment registration entry
                DB::table('subscription_payments')->insert([
                    'subscription_id' => $subscription->id,
                    'company_id'      => $company->id,
                    'payment_method'  => $upgradeRequest->payment_method,
                    'amount'          => $upgradeRequest->amount_paid,
                    'transaction_id'  => $upgradeRequest->transaction_id ?? null,
                    'sender_number'   => $upgradeRequest->number ?? null,
                    'account_number'  => null,
                    'bank_name'       => null,
                    'status'          => 'pending',
                    'meta'            => json_encode([
                        'account_holder_name' => $upgradeRequest->account_holder_name ?? null,
                        'document_path'       => $upgradeRequest->document_path ?? null,
                    ]),
                    'paid_at'         => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // Sync pricing package code directly to company model
                $company->pricing_package_id = $package->id;
                $company->save();

                // ── Trigger affiliate commission (same as normal payment approval) ──
                try {
                    app(ReferralService::class)->processSubscriptionCommission(
                        $subscription,
                        (float) $subscription->amount_paid
                    );
                } catch (\Exception $e) {
                    Log::error('Referral commission failed on upgrade approval: ' . $e->getMessage());
                }
            }

            LogHelper::statusChanged(
                'upgrade_request',
                $upgradeRequest->id,
                $upgradeRequest->company_id,
                'Status changed to ' . $status
            );

            DB::commit();
            Log::info('Upgrade request status updated and synchronized successfully.', ['request_id' => $id]);

            return $upgradeRequest->load(['company', 'pricingPackage']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status change failed: ' . $e->getMessage());
            throw ApiException::serverError($e->getMessage() ?: 'Failed to update request status.');
        }
    }
}

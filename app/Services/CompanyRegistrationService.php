<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Mail\VerifyOtpEmail;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\DomainSetup;
use App\Models\EmailVerification;
use App\Models\Permission;
use App\Models\Pricing;
use App\Models\PricingPackage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserLoginHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompanyRegistrationService
{
    public function getActivePricings()
    {
        return PricingPackage::with('tiers')->where('status', Status::Active->value)
            ->get();
    }

    /**
     * Step 1: Create company and admin user
     */

    public function registerBasic(array $data): array
    {
        DB::beginTransaction();
        try {
            $company = Company::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'business_type' => $data['business_type'] ?? 1,
                'status'        => Status::Draft->value,
            ]);

            LogHelper::created('company', $company->id, $company->id);

            $user = User::create([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'password'   => Hash::make($data['password']),
                'company_id' => $company->id,
                'status'     => Status::Draft->value,
                'is_primary' => 1,

            ]);

            LogHelper::created('user', $user->id, $company->id);

            // ==========================================
            // 🟢 NEW: LOGIN THE USER IMMEDIATELY
            // ==========================================
            $history = UserLoginHistory::create([
                'company_id' => $user->company_id,
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at'   => now(),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            // Return Token & User data just like standard login
            return [
                'registration_id' => $company->id,
                'token'           => $token,
                'login_id'        => $history->id,
                'user'            => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'company_id'  => $user->company_id,
                    'role'        => $user->role,
                    'status'      => $user->status,
                    'profile'     => $user->profile,
                    'profile_url' => $user->profile_url,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Basic registration failed: ' . $e->getMessage());
            throw ApiException::serverError('Account creation failed. Please try again.');
        }
    }

    /**
     * Step 3: Create subscription, site settings, and send OTP
     */
    public function registerSubscription(array $data): void
    {
        DB::beginTransaction();
        try {
            $company = Company::findOrFail($data['registration_id']);
            $billing = $data['billing_cycle'] ?? 'monthly';

            // Load package with the matching tier
            $pricing = PricingPackage::with(['tiers' => function ($q) use ($billing) {
                $q->where('billing_cycle', $billing);
            }])->findOrFail($data['pricing_package_id']);

            $tier = $pricing->tiers->first();

            if (!$tier) {
                throw new \Exception("No pricing tier found for billing cycle: {$billing}");
            }

            $amountPaid = $tier->discount_price > 0 && $tier->discount_price < $tier->regular_price
                ? $tier->discount_price
                : $tier->regular_price;

            $now       = Carbon::now();
            $trialDays = (int) $pricing->trial_days;

            $trialEnds = $trialDays > 0
                ? $now->copy()->addDays($trialDays)
                : null;

            $endsAt = match ($billing) {
                'yearly'    => $now->copy()->addYear(),
                'quarterly' => $now->copy()->addMonths(3),
                default     => $now->copy()->addMonth(),   // monthly
            };

            $subscription = CompanySubscription::create([
                'company_id'         => $company->id,
                'pricing_package_id' => $pricing->id,
                'pricing_tier_id'    => $tier->id,   // store which tier was used

                'amount_paid'        => $amountPaid,
                'payment_method'     => $data['payment_method'] ?? 'card',
                'payment_status'     => 'pending',
                'trial_ends_at'      => $trialEnds,
                'starts_at'          => $now,
                'ends_at'            => $endsAt,
                'status'             => Status::Active->value,
            ]);
            if (in_array($data['payment_method'], ['manual', 'bank'])) {
                $documentPath = null;

                if (isset($data['document']) && $data['document'] instanceof \Illuminate\Http\UploadedFile) {
                    $documentPath = $data['document']->store('payment_documents', 'public');
                }

                DB::table('subscription_payments')->insert([
                    'subscription_id' => $subscription->id,
                    'company_id'      => $company->id,
                    'payment_method'  => $data['payment_method'],
                    'amount'          => $amountPaid,
                    'transaction_id'  => $data['transaction_id'] ?? null,
                    'sender_number'   => $data['number'] ?? null,
                    'account_number'  => null,
                    'bank_name'       => null,
                    'status'          => 'pending',
                    'meta'            => json_encode([
                        'account_holder_name' => $data['account_holder_name'] ?? null,
                        'document_path'       => $documentPath
                    ]),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
            $company->pricing_package_id = $subscription->pricing_package_id;
            $company->update();
            SiteSetting::create([
                'company_id' => $company->id,
                'shop_name'  => $company->name,
                'email'      => $company->email,
                'phone'      => $company->phone,
            ]);

            // We only need ONE OTP since user & company share the same email
            $otp = $this->createOtpRecord($company->id, 'user', $company->email);

            DB::commit();

            // Send email after commit
         //   $this->sendOtpEmail($company->name, $company->email, $otp, 'user');
            Log::info("Otp {$otp} generated for company_id: {$company->id} and sent to email: {$company->email}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription registration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to process subscription.');
        }
    }
    public function registerBasicSettings(array $data): array
    {
        DB::beginTransaction();
        try {
            $company = Company::findOrFail($data['registration_id']);

            // Insert subdomain into DomainSetup
            DomainSetup::create([
                'company_id' => $company->id,
                'sub_domain' => $data['sub_domain'],
            ]);

            // Update language & currency in SiteSettings
            SiteSetting::where('company_id', $company->id)
                ->update([
                    'lang'     => $data['lang'],
                    'currency' => $data['currency'],
                ]);



            // Activate Company
            $company->update([
                'status'               => Status::Active->value,
            ]);

            // Activate the Company's primary User
            User::where('company_id', $company->id)
                ->update(['status' => Status::Active->value]);

            DB::commit();

            Log::info("Basic settings saved and company_id: {$company->id} marked as Active.");

            $user = User::where('company_id', $company->id)
                ->first()
                ->load(['company.pricingPackage', 'roles.permissions']);

            return [
                'permissions'          => $this->resolvePermissions($user),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Basic settings registration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save basic settings.');
        }
    }

    private function resolvePermissions(User $user): array
    {
        if ($user->is_super_admin) {
            if ($user->roles->isNotEmpty()) {
                $perms = [];
                foreach ($user->roles as $role) {
                    foreach ($role->permissions as $permission) {
                        $perms[] = $permission->name;
                    }
                }
                return array_values(array_unique($perms));
            }
            return Permission::where('type', 'superadmin')->pluck('name')->toArray();
        }

        $featureKeys = [];
        $company = $user->company;

        if ($company && $company->pricingPackage) {
            $featureKeys = $company->pricingPackage->features ?? [];
            if (is_string($featureKeys)) {
                $featureKeys = json_decode($featureKeys, true) ?? [];
            }
        }

        if (empty($featureKeys)) return [];

        if ($user->roles->isNotEmpty()) {
            $roleIds = $user->roles->pluck('id')->toArray();
            return Permission::where('type', 'company')
                ->whereIn('feature_dependency', $featureKeys)
                ->whereHas('roles', fn($q) => $q->whereIn('roles.id', $roleIds))
                ->pluck('name')
                ->toArray();
        }

        return Permission::where('type', 'company')
            ->whereIn('feature_dependency', $featureKeys)
            ->pluck('name')
            ->toArray();
    }

    public function verifyOtp(int $registrationId, string $type, string $otp): void
    {
        $record = EmailVerification::where('company_id', $registrationId)
            ->where('type', $type)
            ->latest()
            ->first();

        if (! $record) throw ApiException::notFound('Verification record not found.');
        if ($record->isVerified()) throw ApiException::badRequest('This email is already verified.');
        if ($record->isExpired()) throw ApiException::badRequest('Code has expired. Please request a new one.');
        if ($record->otp !== $otp) throw ApiException::badRequest('Invalid verification code.');

        $record->update(['verified_at' => Carbon::now()]);

        User::where('company_id', $registrationId)
            ->where('email', $record->email)
            ->update([
                'email_verified_at' => Carbon::now(),
                'status' => Status::Pending->value,
            ]);

        Company::where('id', $registrationId)->update(['status' => Status::Pending->value]);

        Log::info("Email verified for company_id: {$registrationId} and email: {$record->email}");
    }

    public function resendOtp(int $registrationId, string $type): void
    {
        $company = Company::findOrFail($registrationId);
        $record = EmailVerification::where('company_id', $registrationId)
            ->where('type', $type)
            ->latest()
            ->first();

        if (! $record) throw ApiException::notFound('Verification record not found.');
        if ($record->isVerified()) throw ApiException::badRequest('This email is already verified.');

        $newOtp = $this->generateOtp();
        $record->update([
            'otp'        => $newOtp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $this->sendOtpEmail($company->name, $record->email, $newOtp, $type);
        Log::info("New OTP {$newOtp} generated for company_id: {$company->id} and sent to email: {$record->email}");
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function createOtpRecord(int $companyId, string $type, string $email): string
    {
        //  $otp = $this->generateOtp();
        $otp = 123456;
        EmailVerification::where('company_id', $companyId)->where('type', $type)->whereNull('verified_at')->delete();
        EmailVerification::create([
            'company_id' => $companyId,
            'type'       => $type,
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
        return $otp;
    }

    private function sendOtpEmail(string $companyName, string $toEmail, string $otp, string $type): void
    {
        try {
            Mail::to($toEmail)->send(new VerifyOtpEmail($companyName, $otp, $type));
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$toEmail}: " . $e->getMessage());
        }
    }
}

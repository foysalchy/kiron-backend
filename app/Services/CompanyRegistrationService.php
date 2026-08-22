<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\SystemPageType;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Mail\VerifyOtpEmail;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\DomainSetup;
use App\Models\EmailTemplate;
use App\Models\EmailVerification;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\Pricing;
use App\Models\PricingPackage;
use App\Models\SiteSetting;
use App\Models\SmsTemplate;
use App\Models\SystemPage;
use App\Models\User;
use App\Models\UserLoginHistory;
use App\Models\Warehouse;
use Carbon\Carbon;
use App\Services\SmsSendService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompanyRegistrationService
{
    public function __construct(
        private SmsSendService $smsSendService
    ) {}
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

            $history = UserLoginHistory::create([
                'company_id' => $user->company_id,
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at'   => now(),
            ]);
            $shop_name = $company->name;
            foreach (SystemPageType::companyScoped() as $type) {

                $seo = $this->getDefaultSeoData($type, $shop_name);

                SystemPage::create([
                    'company_id'    => $company->id,
                    'page_type'     => $type,
                    'title'    => $seo['meta_title'],
                    'meta_title'    => $seo['meta_title'],
                    'meta_description' => $seo['meta_description'],
                    'meta_keywords' => $seo['meta_keywords'],
                    'description'   => $seo['description'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'registration_id' => $company->id,
                'token'           => $token,
                'login_id'        => $history->id,
                'user'            => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'company_id'        => $user->company_id,
                    'role'              => 'User', // primary role, roles relation eventually assign hobe
                    'is_super_admin'    => false,
                    'status'            => $user->status,
                    'setup_complete'    => false,
                    'billing_required'  => false,
                    'profile'           => $user->profile,
                    'profile_url'       => $user->profile_url,
                    'company'           => $company,
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
            $otp = $this->createOtpRecord($company->id, 'user', $company->email,'email');

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
                'template_name' => 'template1'
            ]);

            // Update language & currency in SiteSettings
            SiteSetting::where('company_id', $company->id)
                ->update([
                    'lang'     => $data['lang'],
                    'currency' => $data['currency'],
                    'currency_position' => $data['currency_position'],
                ]);

            if (isset($data['manage_warehouse'])) {
                $manageWarehouse = (bool) $data['manage_warehouse'];

                $company->update(['manage_warehouse' => $manageWarehouse]);

                if (!$manageWarehouse) {
                    $exists = Warehouse::where('company_id', $company->id)
                        ->where('is_default', 1)
                        ->exists();

                    if (!$exists) {
                        $warehouse = Warehouse::create([
                            'company_id' => $company->id,
                            'name'       => 'Default Warehouse',
                            'location'   => null,
                            'is_default' => 1,
                            'status'     => Status::Active->value,
                        ]);

                        $company->update(['default_warehouse_id' => $warehouse->id]);
                    }
                }
            }

            // Activate Company
            $company->update([
                'status' => Status::Active->value,
                'theme_template' => [
                    'id' => "1",
                    'footer_color' => "#dd3636",
                    'header_color' => "#c82828",
                    'primary_color' => "#ffffff",
                    'secondary_color' => "#000",
                    'footer_text_color' => "#c9c0c0",
                    'header_text_color' => "#ffffff",
                    'primary_text_color' => "#000000",
                    'secondary_text_color' => "#1e40af",
                ],
            ]);

            User::where('company_id', $company->id)
                ->update([
                    'status'         => Status::Active->value,
                    'setup_complete' => true, // ⚠️ ei column na thakle migration lagbe, note niche
                ]);

            LeadStatus::create([
                'company_id' => $company->id,
                'name' => "Succeffully Converted",
                'is_default' => 1,
                'status' => Status::Active->value
            ]);
            $smsTemplates = [
                ['title' => 'Order Place', 'slug' => 'order-place', 'description' => 'Your order has been placed successfully.'],
                ['title' => 'Order Shipped', 'slug' => 'order-shipped',  'description' => 'Your order has been shipped.'],
                ['title' => 'Order Confirm', 'slug' => 'order-confirm',  'description' => 'Your order has been confirmed.'],
                ['title' => 'Order Delivered', 'slug' => 'order-delivered', 'description' => 'Your order has been delivered successfully.'],
                ['title' => 'Cancel Order',  'slug' => 'cancel-order',  'description' => 'Your order has been cancelled.'],
                ['title' => 'Follow Up',    'slug' => 'follow-up',   'description' => 'This is a follow-up message regarding your order.'],
            ];

            foreach ($smsTemplates as $template) {
                SmsTemplate::create([
                    'company_id'  => $company->id,
                    'title'       => $template['title'],
                    'slug'       => $template['slug'],
                    'description' => $template['description'],
                    'is_default' => 1,
                    'status'      => Status::Active->value,
                ]);
            }
            $emailTemplates = [
                ['title' => 'Order Place', 'slug' => 'order-place', 'description' => 'Your order has been placed successfully.'],
                ['title' => 'Order Shipped', 'slug' => 'order-shipped', 'description' => 'Your order has been shipped.'],
                ['title' => 'Order Confirm', 'slug' => 'order-confirm', 'description' => 'Your order has been confirmed.'],
                ['title' => 'Order Delivered', 'slug' => 'order-delivered', 'description' => 'Your order has been delivered successfully.'],
                ['title' => 'Cancel Order', 'slug' => 'cancel-order', 'description' => 'Your order has been cancelled.'],
                ['title' => 'Follow Up', 'slug' => 'follow-up', 'description' => 'This is a follow-up message regarding your order.'],
            ];

            foreach ($emailTemplates as $template) {
                EmailTemplate::create([
                    'company_id'  => $company->id,
                    'subject'       => $template['title'],
                    'slug'        => $template['slug'],
                    'body' => $template['description'],
                    'is_default'  => 1,
                    'status'      => Status::Active->value,
                ]);
            }
            DB::commit();

            Log::info("Basic settings saved and company_id: {$company->id} marked as Active.");

            $user = User::where('company_id', $company->id)
                ->with(['company.pricingPackage', 'roles.permissions'])
                ->first();

            return [
                'user'        => $user,
                'permissions' => $this->resolvePermissions($user),
                'company'     => $company->load('pricingPackage'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Basic settings registration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save basic settings.');
        }
    }
    /**
     * Unified Seller Registration - Merging all registration phases into one single atomic transaction.
     */
    public function registerSeller(array $data, $request): array
    {
        DB::beginTransaction();
        try {
            // 1. Create the Company
            $company = Company::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'business_type' => $data['business_type'] ?? 1,
                'status'        => Status::Active->value, // Account activated immediately on success
            ]);

            LogHelper::created('company', $company->id, $company->id);

            // 2. Create the User (Marked as Primary Super-Admin)
            $user = User::create([
                'company_id' => $company->id,
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'password'   => Hash::make($data['password']),
                'status'     => Status::Active->value,
                'is_primary' => 1,
            ]);

            LogHelper::created('user', $user->id, $company->id);



            // 4. Process Subscription pricing tier details and durations
            $billing = $data['billing_cycle'] ?? 'monthly';

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
                default     => $now->copy()->addMonth(),
            };

            // 5. Create Company Subscription using correct pricing_tier_id relation
            $subscription = CompanySubscription::create([
                'company_id'         => $company->id,
                'pricing_package_id' => $pricing->id,
                'pricing_tier_id'    => $tier->id,
                'amount_paid'        => $amountPaid,
                'payment_method'     => $data['payment_method'] ?? 'card',
                'payment_status'     => 'pending',
                'trial_ends_at'      => $trialEnds,
                'starts_at'          => $now,
                'ends_at'            => $endsAt,
                'status'             => Status::Active->value,
            ]);

            // 6. Handle Manual / Bank receipt upload using FileUploadHelper
            if (in_array($data['payment_method'], ['manual', 'bank'])) {
                $documentPath = null;

                if (isset($data['document']) && $data['document'] instanceof \Illuminate\Http\UploadedFile) {
                    $documentPath = FileUploadHelper::uploadImage(
                        $data['document'],
                        'payment_documents'
                    );
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

            // Sync subscription changes back to company profile
            $company->pricing_package_id = $subscription->pricing_package_id;
            $company->update();

            // 7. Create SiteSetting Entry (with language and currency parameters embedded)
            SiteSetting::create([
                'company_id' => $company->id,
                'shop_name'  => $company->name,
                'email'      => $company->email,
                'phone'      => $company->phone,
                'lang'       => $data['lang'],
                'currency'   => $data['currency'],
            ]);

            // 8. Create DomainSetup Entry
            DomainSetup::create([
                'company_id' => $company->id,
                'sub_domain' => $data['sub_domain'],
            ]);

            // 9. Process Warehouse Setup
            if (isset($data['manage_warehouse'])) {
                $manageWarehouse = (bool) $data['manage_warehouse'];
                $company->update(['manage_warehouse' => $manageWarehouse]);

                if (!$manageWarehouse) {
                    $exists = Warehouse::where('company_id', $company->id)
                        ->where('is_default', 1)
                        ->exists();

                    if (!$exists) {
                        $warehouse = Warehouse::create([
                            'company_id' => $company->id,
                            'name'       => 'Default Warehouse',
                            'location'   => null,
                            'is_default' => 1,
                            'status'     => Status::Active->value,
                        ]);

                        $company->update(['default_warehouse_id' => $warehouse->id]);
                    }
                }
            }



            DB::commit();

            Log::info("Unified registration complete for company_id: {$company->id} and user_id: {$user->id}");

            // Load and resolve freshly registered relationships and permissions
            $freshUser = User::where('company_id', $company->id)
                ->with(['company.pricingPackage', 'roles.permissions'])
                ->first();

            return [

                'user' => [
                    'id'          => $freshUser->id,
                    'name'        => $freshUser->name,
                    'email'       => $freshUser->email,
                    'company_id'  => $freshUser->company_id,

                    'company'     => $company->load('pricingPackage'),
                ],
                'permissions' => $this->resolvePermissions($freshUser),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unified registration failed: ' . $e->getMessage());
            throw ApiException::serverError($e->getMessage() ?: 'Registration failed. Please try again.');
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

    public function verifyOtp(int $registrationId, string $type, string $otp, string $method): User
    {
        $record = EmailVerification::where('company_id', $registrationId)
            ->where('type', $type)
            ->where('method', $method)
            ->latest()
            ->first();

        if (! $record) throw ApiException::notFound('Verification record not found.');
        if ($record->isVerified()) {
            throw ApiException::badRequest(
                $method === 'sms'
                    ? 'This phone number is already verified.'
                    : 'This email is already verified.'
            );
        }
        if ($record->isExpired()) throw ApiException::badRequest('Code has expired. Please request a new one.');
        if ($record->otp !== $otp) throw ApiException::badRequest('Invalid verification code.');

        $record->update(['verified_at' => Carbon::now()]);

        $user = User::where('company_id', $registrationId)
            ->where('is_primary', 1)
            ->first();

        if (! $user) {
            throw ApiException::notFound('User not found for this company.');
        }

        $updateData = [];
        if ($method === 'email') {
            $updateData['email_verified_at'] = Carbon::now();
        } else {
            $updateData['phone_verified_at'] = Carbon::now();
        }

        // ✅ user.status shudhu tokhoni Pending banabo jodi user ekhono
        // Draft/notun obosthai thake — already Active user er status change korbo na
        if ($user->status !== Status::Active->value) {
            $updateData['status'] = Status::Pending->value;
        }
        $user->update($updateData);

        $company = Company::find($registrationId);
        if ($company && $company->status !== Status::Active->value) {
            $company->update(['status' => Status::Pending->value]);
        }

        Log::info("{$method} verified for company_id: {$registrationId}");

        return $user->load('company');
    }

    public function resendOtp(int $registrationId, string $type, string $method = 'email'): void
    {
        $company = Company::findOrFail($registrationId);

        $record = EmailVerification::where('company_id', $registrationId)
            ->where('type', $type)
            ->where('method', $method)
            ->latest()
            ->first();

        // record na thakle notun banate hobe (dashboard theke first-time verify korar case)
        if (!$record) {
            $record = EmailVerification::create([
                'company_id' => $registrationId,
                'type'       => $type,
                'method'     => $method,
                'email'      => $method === 'email' ? $company->email : $company->phone,
                'otp'        => $this->generateOtp(),
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);
        } else {
            if ($record->isVerified()) {
                throw ApiException::badRequest(
                    $method === 'sms'
                        ? 'This phone number is already verified.'
                        : 'This email is already verified.'
                );
            }

            $record->update([
                'otp'        => $this->generateOtp(),
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);
        }

        if ($method === 'sms') {
            $this->sendopt($company->phone, $record->otp);
            Log::info("New OTP {$record->otp} generated for company_id: {$company->id} and sent to phone: {$company->phone}");
        } else {
            $this->sendOtpEmail($company->name, $company->email, $record->otp, $type);
            Log::info("New OTP {$record->otp} generated for company_id: {$company->id} and sent to email: {$company->email}");
        }
    }
    public function sendopt($phone, $otp)
    {
        $message = "Your Dorja.io verification code is {$otp}. Use it to complete your registration. Valid for 5 minutes. Do not share this code.";
        $this->smsSendService->sendToGateway([$phone], $message);
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // return 123456;
    }

    private function createOtpRecord(int $companyId, string $type, string $email,string $method): string
    {
        $otp = $this->generateOtp();

        EmailVerification::where('company_id', $companyId)->where('type', $type)->whereNull('verified_at')->delete();
        EmailVerification::create([
            'company_id' => $companyId,
            'type'       => $type,
            'email'      => $email,
            'method'      => $method,
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

    private function getDefaultSeoData($type, $shop_name)
    {
        $data = [

            'home_page' => [
                'meta_title' => "Online Shopping at {$shop_name} | Shop Latest Products",
                'meta_description' => "Shop quality products at the best prices from {$shop_name}. Explore fashion, electronics, lifestyle, beauty, home essentials and more with secure payment and fast delivery.",
                'meta_keywords' => "online shopping, ecommerce, online shop, buy products online, best price, shopping website, {$shop_name}",
                'description' => "Shop your favorite products from the comfort of your home with {$shop_name}. Explore a wide range of quality products across fashion, electronics, beauty, lifestyle, home essentials and more. We offer competitive prices, secure payment options, reliable delivery and a convenient online shopping experience. Discover the latest products and exciting deals every day."
            ],

            'blog_list' => [
                'meta_title' => "Shopping Blog | Tips, Guides & Latest Trends | {$shop_name}",
                'meta_description' => "Explore the latest shopping tips, product guides, buying advice, trends, reviews and useful information from {$shop_name} to help you make better purchase decisions.",
                'meta_keywords' => "shopping blog, product guides, buying tips, product reviews, latest trends, ecommerce blog, {$shop_name}",
                'description' => "Welcome to the {$shop_name} shopping blog, where you can discover useful buying guides, product tips, industry trends, product information and helpful recommendations. Whether you are looking for the right product or want to stay updated with the latest trends, our articles are designed to help you shop smarter."
            ],

            'brand_list' => [
                'meta_title' => "Top Brands | Shop Trusted Brands at {$shop_name}",
                'meta_description' => "Discover products from popular and trusted brands at {$shop_name}. Browse our brand collection and find your favorite products at competitive prices with convenient delivery.",
                'meta_keywords' => "top brands, trusted brands, branded products, popular brands, buy branded products, online brands, {$shop_name}",
                'description' => "Explore our collection of products from trusted and popular brands at {$shop_name}. Browse products by brand to easily find your preferred items and discover new favorites. We bring together quality products from a variety of brands to make your online shopping experience simple and convenient."
            ],

            'contact_us' => [
                'meta_title' => "Contact Us | Customer Support | {$shop_name}",
                'meta_description' => "Need help with your order or shopping experience? Contact the {$shop_name} customer support team for assistance with orders, products, delivery, payment and other inquiries.",
                'meta_keywords' => "contact us, customer support, ecommerce support, online shopping help, order support, customer service, {$shop_name}",
                'description' => "Have a question about a product, order, payment or delivery? The {$shop_name} customer support team is here to help. Contact us through our available support channels and we will do our best to assist you with your shopping experience."
            ],

            'shop_product' => [
                'meta_title' => "Shop Online | Buy Quality Products at Best Prices | {$shop_name}",
                'meta_description' => "Browse and shop a wide range of quality products at competitive prices from {$shop_name}. Find your favorite products with secure payment and reliable delivery.",
                'meta_keywords' => "shop online, buy products online, ecommerce shop, online products, best price products, online shopping, {$shop_name}",
                'description' => "Discover our wide selection of products across multiple categories at {$shop_name}. Compare products, explore different options and find the right products for your needs. Enjoy competitive prices, convenient online ordering, secure payment and reliable delivery."
            ],

            'category_list' => [
                'meta_title' => "Product Categories | Browse All Categories | {$shop_name}",
                'meta_description' => "Explore all product categories at {$shop_name} and easily find what you need. Browse fashion, electronics, beauty, lifestyle, home essentials and more.",
                'meta_keywords' => "product categories, shopping categories, ecommerce categories, online shopping categories, products, {$shop_name}",
                'description' => "Browse the complete range of product categories at {$shop_name} and find everything you need in one place. From fashion and electronics to beauty, lifestyle, home essentials and more, our organized categories make it easy to discover products and shop conveniently."
            ],

            'cart' => [
                'meta_title' => "Shopping Cart | Review Your Products | {$shop_name}",
                'meta_description' => "Review your selected products, update quantities and check your order summary before proceeding to secure checkout at {$shop_name}.",
                'meta_keywords' => "shopping cart, online cart, ecommerce cart, buy products, order cart, {$shop_name}",
                'description' => "Review the products you have selected from {$shop_name} before completing your purchase. You can update product quantities, remove unwanted items and review your order summary."
            ],

            'checkout' => [
                'meta_title' => "Secure Checkout | Complete Your Order | {$shop_name}",
                'meta_description' => "Complete your purchase through the secure checkout at {$shop_name}. Enter your delivery details, choose your preferred payment method and place your order easily.",
                'meta_keywords' => "checkout, secure checkout, online payment, place order, ecommerce checkout, online order, {$shop_name}",
                'description' => "Complete your order quickly and securely through the {$shop_name} checkout process. Provide your delivery information, review your order details and select your preferred payment method."
            ],

            'login' => [
                'meta_title' => "Login | Sign In to Your Account | {$shop_name}",
                'meta_description' => "Sign in to your {$shop_name} account to manage orders, track purchases, save products and enjoy a personalized online shopping experience.",
                'meta_keywords' => "login, customer login, account login, ecommerce login, online shopping account, {$shop_name}",
                'description' => "Sign in to your {$shop_name} account to access your orders, manage your profile, track purchases and enjoy a more personalized shopping experience."
            ],

            'register' => [
                'meta_title' => "Create Account | Register for Online Shopping | {$shop_name}",
                'meta_description' => "Create your {$shop_name} account and enjoy a faster, easier shopping experience. Manage orders, track purchases and securely save your information for future orders.",
                'meta_keywords' => "register, create account, signup, ecommerce registration, online shopping account, customer registration, {$shop_name}",
                'description' => "Create your account with {$shop_name} and make your online shopping experience faster and more convenient. Manage your profile, view order history, track purchases and save your information for future orders."
            ],

        ];

        return $data[$type] ?? [
            'meta_title' => $shop_name,
            'meta_description' => "Shop online at {$shop_name}.",
            'meta_keywords' => "online shopping, {$shop_name}",
            'description' => "Welcome to {$shop_name}, your online shopping destination."
        ];
    }
}

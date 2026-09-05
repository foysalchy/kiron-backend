<?php

namespace Database\Seeders;

use App\Models\ReferralGroup;
use App\Models\ReferralPartner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReferralGroupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Non-referral / Standard Group
        $standard = ReferralGroup::firstOrCreate(
            ['slug' => 'standard-partner'],
            [
                'name'                    => 'Standard Partner (20%)',
                'default_commission_rate' => 20.00,
                'buyer_discount_rate'     => 5.00,
                'commission_type'         => 'percentage',
                'is_tiered'               => true,
                'description'             => 'Standard public referral partner with volume milestone ranks.',
                'status'                  => 1,
            ]
        );

        $standard->tiers()->delete();
        $standard->tiers()->createMany([
            ['tier_name' => 'Bronze Rank (1-100 sales)', 'min_sales' => 1, 'max_sales' => 100, 'commission_rate' => 20.00],
            ['tier_name' => 'Silver Rank (101-300 sales)', 'min_sales' => 101, 'max_sales' => 300, 'commission_rate' => 30.00],
            ['tier_name' => 'Gold Rank (301+ sales)', 'min_sales' => 301, 'max_sales' => null, 'commission_rate' => 35.00],
        ]);

        // 2. CA Referral Group
        $ca = ReferralGroup::firstOrCreate(
            ['slug' => 'ca-referral'],
            [
                'name'                    => 'CA & Accounting Partner (30%)',
                'default_commission_rate' => 30.00,
                'buyer_discount_rate'     => 10.00,
                'commission_type'         => 'percentage',
                'is_tiered'               => true,
                'description'             => 'Chartered Accountants & business consultants partner program.',
                'status'                  => 1,
            ]
        );

        $ca->tiers()->delete();
        $ca->tiers()->createMany([
            ['tier_name' => 'Tier 1 (1-50 sales)', 'min_sales' => 1, 'max_sales' => 50, 'commission_rate' => 25.00],
            ['tier_name' => 'Tier 2 (51-150 sales)', 'min_sales' => 51, 'max_sales' => 150, 'commission_rate' => 30.00],
            ['tier_name' => 'Tier 3 (151+ sales)', 'min_sales' => 151, 'max_sales' => null, 'commission_rate' => 35.00],
        ]);

        // 3. Employee Sales Group
        $emp = ReferralGroup::firstOrCreate(
            ['slug' => 'employee-sales'],
            [
                'name'                    => 'Employee Sales Team (15%)',
                'default_commission_rate' => 15.00,
                'buyer_discount_rate'     => 5.00,
                'commission_type'         => 'percentage',
                'is_tiered'               => true,
                'description'             => 'Direct employee sales & field marketing agent commission.',
                'status'                  => 1,
            ]
        );

        $emp->tiers()->delete();
        $emp->tiers()->createMany([
            ['tier_name' => 'Agent Level 1 (1-100 sales)', 'min_sales' => 1, 'max_sales' => 100, 'commission_rate' => 15.00],
            ['tier_name' => 'Agent Level 2 (101-300 sales)', 'min_sales' => 101, 'max_sales' => 300, 'commission_rate' => 20.00],
            ['tier_name' => 'Top Producer (301+ sales)', 'min_sales' => 301, 'max_sales' => null, 'commission_rate' => 25.00],
        ]);

        // Create a Demo Partner for quick testing if none exists
        if (!ReferralPartner::where('email', 'partner@dorja.io')->exists()) {
            ReferralPartner::create([
                'referral_group_id' => $standard->id,
                'name'              => 'Demo Partner',
                'email'             => 'partner@dorja.io',
                'phone'             => '01700000000',
                'password'          => Hash::make('12345678'),
                'referral_code'     => 'REF-DEMO2026',
                'wallet_balance'    => 2500.00,
                'total_earned'      => 5000.00,
                'total_withdrawn'   => 2500.00,
                'payout_method'     => 'bkash',
                'payout_details'    => ['account' => '01700000000', 'type' => 'Personal'],
                'status'            => 1,
            ]);
        }
    }
}

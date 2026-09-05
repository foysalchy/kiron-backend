<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed TallyPrime Default Account Groups and Chart of Accounts
        $this->call(DefaultAccountingSeeder::class);

        // Seed Default Referral Groups, Tier Milestones & Partner
        $this->call(ReferralGroupSeeder::class);
    }
}

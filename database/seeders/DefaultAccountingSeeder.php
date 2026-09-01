<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\DefaultAccountingSeederService;
use Illuminate\Database\Seeder;

class DefaultAccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            // Seed for default ID 1 if no companies in DB
            DefaultAccountingSeederService::seedDefaultAccountsForCompany(1);
        } else {
            foreach ($companies as $company) {
                DefaultAccountingSeederService::seedDefaultAccountsForCompany($company->id);
            }
        }
    }
}

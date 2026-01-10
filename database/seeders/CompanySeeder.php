<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create specific companies
        Company::create([
            'name' => 'Tech Solutions Ltd',
            'email' => 'info@techsolutions.com',
            'phone' => '+1234567890',
            'alternative_phone' => '+0987654321',
            'address' => '123 Tech Street, Silicon Valley, CA',
            'business_type' => 1,
            'status' => 1,
        ]);

        Company::create([
            'name' => 'Retail Plus Inc',
            'email' => 'contact@retailplus.com',
            'phone' => '+1122334455',
            'address' => '456 Commerce Ave, New York, NY',
            'business_type' => 2,
            'status' => 1,
        ]);

     
    }
}
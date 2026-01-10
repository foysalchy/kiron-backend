<?php

namespace Database\Seeders;

use App\Models\Party;
use Illuminate\Database\Seeder;

class PartySeeder extends Seeder
{
    public function run(): void
    {
        // Create specific companies
        Party::create([
            'name' => 'kalam ahmed',
            'company_id' => 1,
            'type' => 1,
            'email' => 'info@techsolutions.com',
            'phone' => '+1234567890',
            'alternative_phone' => '+0987654321',
            'address' => '123 Tech Street, Silicon Valley, CA',
            'balance' => 1000,
            'status' => 1,
        ]);

        Party::create([
            'name' => 'Foysal Ahmed',
            'company_id' => 1,
            'type' => 2,
            'email' => 'contact@retailplus.com',
            'phone' => '+1122334455',
            'address' => '456 Commerce Ave, New York, NY',
            'balance' => -1000,
            'status' => 1,
        ]);
    }
}

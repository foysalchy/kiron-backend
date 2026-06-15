<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'           => 'Super Admin',
            'company_id'     => null,
            'email'          => 'admin@gmail.com',
            'phone'          => '01700000000',
            'password'       => Hash::make('12345678'),
            'role'           => 'super_admin',
            'is_super_admin' => true,
            'is_primary'     => true,
            'status'         => 1,
        ]);
    }
}
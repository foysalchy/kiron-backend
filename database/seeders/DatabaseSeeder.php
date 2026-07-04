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

//  php artisan db:seed --class=SuperAdminSeeder
//          php artisan db:seed --class=PermissionSeeder
//         php artisan db:seed --class=SuperAdminPermissionSeeder
//         php artisan db:seed --class=SystemPageSeeder 

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

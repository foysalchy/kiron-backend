<?php

namespace Database\Seeders;

use App\Enums\SystemPageType;
use App\Models\SystemPage;
use Illuminate\Database\Seeder;

class SystemPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SystemPageType::superAdminScoped() as $type) {
            SystemPage::firstOrCreate([
                'company_id' => null,
                'page_type'  => $type,
            ]);
        }
    }
}
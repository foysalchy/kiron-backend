<?php

namespace App\Services;

use App\Models\PayrollSetting;

class PayrollSettingService
{
    public function getSettings()
    {
        $companyId = auth()->user()->company_id;

        return PayrollSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'late_days_for_penalty'    => 3,
                'penalty_amount_in_days'   => 1.0,
                'has_overtime_allowance'   => false,
                'overtime_rate_multiplier' => 1.0,
                'standard_working_hours'   => 8,
            ]
        );
    }
    public function updateSettings(array $data)
    {


        $companyId = auth()->user()->company_id;

        return PayrollSetting::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );
    }
}

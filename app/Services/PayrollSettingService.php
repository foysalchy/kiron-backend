<?php

namespace App\Services;

use App\Models\PayrollSetting;

class PayrollSettingService
{
    public function getSettings()
    {
        return PayrollSetting::first();
    }

    public function updateSettings(array $data)
    {


        return PayrollSetting::updateOrCreate(

            $data
        );
    }
}

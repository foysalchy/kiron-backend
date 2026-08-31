<?php

namespace App\Services\Production;

use App\Models\ProductionSetting;
use Illuminate\Support\Facades\Auth;

class ProductionSettingService
{
    public function getSettings(): ProductionSetting
    {
        $setting = ProductionSetting::with(['defaultRawMaterialWarehouse', 'defaultFinishedGoodsWarehouse'])->first();
        if (!$setting) {
            $setting = ProductionSetting::create([
                'order_prefix' => 'PO',
                'bom_prefix' => 'BOM',
                'plan_prefix' => 'PP',
                'auto_consume_on_start' => true,
                'require_qc_before_completion' => false,
                'allow_over_consumption' => false,
            ]);
        }
        return $setting;
    }

    public function updateSettings(array $data): ProductionSetting
    {
        $setting = $this->getSettings();
        $setting->update($data);
        return $setting->fresh(['defaultRawMaterialWarehouse', 'defaultFinishedGoodsWarehouse']);
    }
}

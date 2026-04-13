<?php

namespace App\Services;

use App\Models\PricingPackage;

class PricingPackageService
{
    public function store(array $data)
    {
        $tiers = $data['tiers'] ?? [];
        unset($data['tiers']);

        $package = PricingPackage::create($data);

        if (!empty($tiers)) {
            $package->tiers()->createMany($tiers);
        }

        return $package->load('tiers');
    }

    public function update(PricingPackage $package, array $data)
    {
        $tiers = $data['tiers'] ?? [];
        unset($data['tiers']);

        $package->update($data);

        $providedCycles = collect($tiers)->pluck('billing_cycle')->toArray();
        $package->tiers()->whereNotIn('billing_cycle', $providedCycles)->delete();

        foreach ($tiers as $tier) {
            $package->tiers()->updateOrCreate(
                ['billing_cycle' => $tier['billing_cycle']],
                [
                    'regular_price' => $tier['regular_price'],
                    'discount_price' => $tier['discount_price'] ?? null,
                ]
            );
        }

        return $package->fresh('tiers');
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasSaasCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPackage extends Model
{
    use SoftDeletes,HasSaasCache;
    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'multiple_input' => 'array',
    ];
    public static function saasCacheKeys(): array
    {
        return ['saas_home_pricing_plans', 'saas_packages_list'];
    }
    public function tiers()
    {
        return $this->hasMany(PricingTier::class, 'package_id');
    }
    public function companySubcription()
    {
        return $this->hasMany(CompanySubscription::class);
    }
}

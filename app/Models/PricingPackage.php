<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPackage extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'multiple_input' => 'array',
    ];

    public function tiers()
    {
        return $this->hasMany(PricingTier::class, 'package_id');
    }
    public function companySubcription()
    {
        return $this->hasMany(CompanySubscription::class);
    }
}

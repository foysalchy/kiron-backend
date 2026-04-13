<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class PricingPackage extends Model
{

    use CompanyScoped;
    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'multiple_input' => 'array',
    ];

    public function tiers()
    {
        return $this->hasMany(PricingTier::class, 'package_id');
    }
}
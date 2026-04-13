<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    protected $guarded = ['id'];

    public function package()
    {
        return $this->belongsTo(PricingPackage::class, 'package_id');
    }
}

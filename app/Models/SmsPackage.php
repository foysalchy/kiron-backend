<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sms_count', 'price', 'rate_per_sms', 'status'
    ];

    protected static function booted(): void
    {
      
        static::saving(function ($package) {
            if ($package->sms_count > 0) {
                $package->rate_per_sms = round($package->price / $package->sms_count, 4);
            }
        });
    }
}

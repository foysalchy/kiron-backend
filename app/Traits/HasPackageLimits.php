<?php

namespace App\Traits;

use App\Services\LimitService;

trait HasPackageLimits
{
    protected static function bootHasPackageLimits(): void
    {
        static::creating(function ($model) {
      
            if (!isset($model->limitKey)) return;

            LimitService::check($model);
        });
    }
}
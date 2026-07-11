<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Model;

class ChannelConnection extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'business_name',
        'page_id',
        'page_name',
        'ig_id',
        'ig_username',
        'waba_id',
        'phone_number_id',
        'phone_number',
        'access_token',
        'connected_at',
        'is_active',
    ];
    protected $casts = ['connected_at' => 'datetime'];

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? decrypt($v) : null,
            set: fn($v) => $v ? encrypt($v) : null,
        );
    }
}

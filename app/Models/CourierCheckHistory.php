<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class CourierCheckHistory extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'phone',
        'response_data',
        'checked_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'checked_at' => 'datetime',
    ];
}

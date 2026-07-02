<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScoped;

class MenuSetting extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'items',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}

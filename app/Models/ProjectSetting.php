<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    use CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'custom_statuses' => 'array',
        'default_budgets' => 'array',
    ];
}

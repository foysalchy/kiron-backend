<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class OmniSetting extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'auto_assign',
        'away_mode_active',
        'away_message',
        'welcome_mode_active',
        'welcome_message',
        'business_hours_start',
        'business_hours_end',
        'auto_assign_agents',
        'off_days',

    ];
    protected $casts = [
        'off_days'            => 'array',
        'auto_assign' => 'boolean',
        'away_mode_active' => 'boolean',
        'welcome_mode_active' => 'boolean',
        'auto_assign_agents' => 'array',
    ];
}

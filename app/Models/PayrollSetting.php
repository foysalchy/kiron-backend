<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'late_days_for_penalty',
        'penalty_amount_in_days',
        'has_overtime_allowance',
        'overtime_rate_multiplier',
        'standard_working_hours'
    ];
    
    protected $casts = [
        'has_overtime_allowance' => 'boolean',
        'penalty_amount_in_days' => 'float',
        'overtime_rate_multiplier' => 'float',
    ];
}
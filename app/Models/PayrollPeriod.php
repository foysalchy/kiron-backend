<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $table = 'payroll_periods';

    protected $fillable = [
        'payroll_id',
        'period_id',
    ];
}

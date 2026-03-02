<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'period_id',
        'total_days',
        'working_days',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'weekend_days',
        'holiday_days',
        'gross_salary',
        'total_deductions',
        'net_payable',
        'status',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class);
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'pay_head_id',
        'type', // addition, deduction
        'amount'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payHead()
    {
        return $this->belongsTo(PayHead::class, 'pay_head_id');
    }
}
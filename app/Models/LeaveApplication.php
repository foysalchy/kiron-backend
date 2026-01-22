<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'reason',
        'status'
    ];
    protected $hidden = ['deleted_at'];
}

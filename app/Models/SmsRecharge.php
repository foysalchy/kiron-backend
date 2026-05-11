<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsRecharge extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id', 'sms_package_id', 'reference_no',
        'sms_count', 'price', 'rate_per_sms',
        'payment_method', 'transaction_id', 'account_number',
        'note', 'screenshot','status', 'reject_reason',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(SmsPackage::class, 'sms_package_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    protected $fillable = [
        'payslip_id',
        'name',
        'type',
        'amount',
    ];

    // Relationships
    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}

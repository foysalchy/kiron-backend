<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = [
        'company_id',
        'start_date',
        'end_date',
        'amount',
        'new_orders_count',
        'total_orders_count',
        'period',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'purchase_id',
        'amount',
        'payment_date',
        'payment_type',
        'account',
        'reference_no',
        'note',
    ];
   
}

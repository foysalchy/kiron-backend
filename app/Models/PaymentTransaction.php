<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes,CompanyScoped;

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'company_id',
        'party_id',
        'direction',
        'transaction_no',
        'amount',
        'payment_date',
        'payment_mode',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'float',
        'payment_date' => 'datetime',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class SmsWalletTransaction extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id', 'sms_wallet_id', 'type',
        'sms_count', 'rate_per_sms',
        'reference_type', 'reference_id',
        'balance_before', 'balance_after', 'note',
    ];

    public function wallet()
    {
        return $this->belongsTo(SmsWallet::class, 'sms_wallet_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}

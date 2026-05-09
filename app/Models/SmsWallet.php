<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class SmsWallet extends Model
{
    use CompanyScoped;
    protected $fillable = ['company_id', 'sms_count'];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions()
    {
        return $this->hasMany(SmsWalletTransaction::class);
    }

    public function hasSufficientBalance(int $required): bool
    {
        return $this->sms_count >= $required;
    }

    public function currentRate(): float
    {
        $lastRecharge = SmsRecharge::where('status', 1)
            ->latest()
            ->first();

        return $lastRecharge?->rate_per_sms ?? 0;
    }
}

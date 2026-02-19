<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethodType extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'payment_method',
        'status'
    ];
    protected $hidden = ['deleted_at'];
    // Scopes

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
        public function paymentMethod(): HasMany
    {
        return $this->hasMany(CustomerPaymentMethod::class);
    }
}

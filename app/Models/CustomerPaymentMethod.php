<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPaymentMethod extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'icon',
        'method_details',
        'account_holder',
        'account_number',
        'contact_name',
        'phone',
        'status',
    ];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'method_details' => 'array',
    ];
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
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
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function paymentMethodType()
    {
        return $this->belongsTo(PaymentMethodType::class);
    }
    // Accessors
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}

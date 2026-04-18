<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alternative_phone',
        'logo',
        'address',
        'business_type',
        'invoice_template',
        'theme_template',
        'pricing_package_id',
        'status',
    ];



    protected $hidden = [
        'deleted_at',
    ];
    protected $casts = [
        'invoice_template' => 'array',
        'theme_template' => 'array',
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function parties()
    {
        return $this->hasMany(Party::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
    public function orderReturns()
    {
        return $this->hasMany(OrderReturn::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function paySlipManagers(): HasMany
    {
        return $this->hasMany(PaySlipManager::class);
    }
    public function generatePayslips(): HasMany
    {
        return $this->hasMany(GeneratePayslip::class);
    }
    public function pricingPackage()
    {
        return $this->belongsTo(PricingPackage::class, 'pricing_package_id');
    }


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByBusinessType($query, string $type)
    {
        return $query->where('business_type', $type);
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}

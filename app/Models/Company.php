<?php

namespace App\Models;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Traits\HasGlobalLayoutCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasGlobalLayoutCache;

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
        'manage_warehouse',
        'default_warehouse_id',
    ];

    protected $appends = ['theme_settings'];


    protected $hidden = [
        'deleted_at',
    ];
    protected $casts = [
        'invoice_template' => 'array',
        'theme_template' => 'array',
        'manage_warehouse' => 'boolean',
    ];
    protected static function booted()
    {
        static::created(function ($company) {
            CustomerPaymentMethod::create([
                'company_id'     => $company->id,
                'name'           => 'Cash on Delivery',
                'type'           => 'Manual',
                'contact_name'   => 'System',
                'phone'          => $company->phone,
                'is_deletable'   => false,
                'status'         => Status::Active->value,
            ]);
        });
        static::deleting(function ($paymentMethod) {
            if (!$paymentMethod->is_deletable) {
                throw ApiException::forbidden("System default payment methods cannot be deleted.");
            }
        });
    }
    public static function globalLayoutSections(): array
    {
        return ['theme_color'];
    }
    public function domainSetup()
    {
        return $this->hasOne(DomainSetup::class);
    }

    public function getThemeSettingsAttribute()
    {
        $domain = $this->domainSetup;

        if (! $domain) {
            return null;
        }

        return [
            'theme_id'             => $domain->template_name,
            'card_id'              => $domain->product_card_template,
            'primary_color'        => $domain->primary_color,
            'primary_text_color'   => $domain->primary_text_color,
            'secondary_color'      => $domain->secondary_color,
            'secondary_text_color' => $domain->secondary_text_color,
            'header_color'         => $domain->header_color,
            'header_text_color'    => $domain->header_text_color,
            'footer_color'         => $domain->footer_color,
            'footer_text_color'    => $domain->footer_text_color,
            'is_review'            => $domain->is_review,
        ];
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function primaryUser()
    {
        return $this->hasOne(User::class)->where('is_primary', 1);
    }
    public function updateRequests(): HasMany
    {
        return $this->hasMany(CompanyUpdateRequest::class);
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
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
    public function pricingPackage()
    {
        return $this->belongsTo(PricingPackage::class, 'pricing_package_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function latestSubscription()
    {
        return $this->hasOne(CompanySubscription::class)->latestOfMany();
    }

    public function hasActiveAccess(): bool
    {
        $subscription = $this->latestSubscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->isActive() || $subscription->isOnTrial();
    }
    public function currentSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->where('status', Status::Active->value)
            ->latestOfMany();
    }
    public function loginHistories(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class)->latest('login_at')->limit(10);
    }
    public function extraOrderCharges(): HasMany
    {
        return $this->hasMany(ExtraOrderCharge::class);
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

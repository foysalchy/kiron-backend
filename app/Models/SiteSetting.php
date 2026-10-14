<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    use SoftDeletes, CompanyScoped, HasHomepageCache, HasGlobalLayoutCache;


    protected $fillable = [
        'company_id',
        'shop_name',
        'legal_name',
        'alternate_name',
        'title',
        'description',
        'logo',
        'favicon',
        'watermark_status',
        'watermark_logo',
        'phone',
        'inside_charge',
        'outside_charge',
        'alt_phone',
        'email',
        'lang',
        'currency',
        'currency_position',
        'country',
        'inside_charge',
        'outside_charge',
        'corporate_address',
        'store_address',
        'tags',
        'copy_right',
        'sms_forget_password',
        'allow_search_engine_index',
        'status',
        'meta_image',
        'dark_logo',
        'founder_name',
        'founder_designation',
        'established',
        'free_shipping_rules',
        'free_shipping_text',
    ];
    protected $hidden = ['deleted_at'];

    protected $casts = [
        'sms_forget_password' => 'boolean',
        'allow_search_engine_index' => 'boolean',
        'free_shipping_rules' => 'array',
    ];


    protected $appends = ['logo_url', 'favicon_url', 'meta_image_url', 'dark_logo_url', 'watermark_logo_url', 'active_free_shipping_rule'];
    public static function homepageCacheKeys(): array
    {
        return ['site_settings_cart', 'site_settings'];
    }
    public static function globalLayoutSections(): array
    {
        return ['setup'];
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
    // Accessors
    public function getWatermarkLogoUrlAttribute(): ?string
    {
        return $this->watermark_logo
            ? Storage::disk('r2')->url($this->watermark_logo)
            : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('r2')->url($this->logo)
            : null;
    }
    // Accessors for Favicon
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon
            ? Storage::disk('r2')->url($this->favicon)
            : null;
    }

    public function getMetaImageUrlAttribute(): ?string
    {
        return $this->meta_image
            ? Storage::disk('r2')->url($this->meta_image)
            : null;
    }

    public function getDarkLogoUrlAttribute(): ?string
    {
        return $this->dark_logo
            ? Storage::disk('r2')->url($this->dark_logo)
            : null;
    }

    /**
     * Clean and compact free shipping rules before saving to database
     */
    public function setFreeShippingRulesAttribute($value): void
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            $this->attributes['free_shipping_rules'] = null;
            return;
        }

        // Keep only clean numerical and boolean fields (strip out verbose names/text)
        $clean = array_values(array_map(function ($r) {
            return [
                'id'      => (int) ($r['id'] ?? 0),
                'qty'     => (int) ($r['qty'] ?? 0),
                'price'   => (float) ($r['price'] ?? 0),
                'enabled' => !empty($r['enabled']),
                'landing' => !empty($r['apply_in_landing'] ?? $r['landing'] ?? false),
            ];
        }, $value));

        $this->attributes['free_shipping_rules'] = json_encode($clean);
    }

    /**
     * Get currently active free shipping rule
     */
    public function getActiveFreeShippingRuleAttribute(): ?array
    {
        $rules = $this->free_shipping_rules;
        if (!is_array($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            if (!empty($rule['enabled'])) {
                // Ensure backward compatible apply_in_landing key
                $rule['apply_in_landing'] = !empty($rule['apply_in_landing'] ?? $rule['landing'] ?? false);
                return $rule;
            }
        }

        return null;
    }

    /**
     * Check if an order qualifies for free shipping
     *
     * @param int $totalQty
     * @param float $subtotal
     * @param bool $isLanding
     * @return bool
     */
    public function isEligibleForFreeShipping(int $totalQty = 0, float $subtotal = 0.0, bool $isLanding = false): bool
    {
        $active = $this->active_free_shipping_rule;
        if (!$active) {
            return false;
        }

        if ($isLanding && empty($active['landing']) && empty($active['apply_in_landing'])) {
            return false;
        }

        $minQty = (int) ($active['qty'] ?? 0);
        $minPrice = (float) ($active['price'] ?? 0);

        // Rule 1: Both Qty and Min Price
        if ($minQty > 0 && $minPrice > 0) {
            return ($totalQty >= $minQty && $subtotal >= $minPrice);
        }

        // Rule 2: Quantity based only
        if ($minQty > 0) {
            return ($totalQty >= $minQty);
        }

        // Rule 3: Price based only
        if ($minPrice > 0) {
            return ($subtotal >= $minPrice);
        }

        return false;
    }
}

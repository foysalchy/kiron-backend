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
        'title',
        'description',
        'logo',
        'favicon',
        'phone',
        'inside_charge',
        'outside_charge',
        'alt_phone',
        'email',
        'lang',
        'currency',
        'inside_charge',
        'outside_charge',
        'corporate_address',
        'store_address',
        'tags',
        'copy_right',
        'status',
        'meta_image',
        'founder_name',
        'founder_designation',
        'established',
    ];
    protected $hidden = ['deleted_at'];


    protected $appends = ['logo_url', 'favicon_url'];
    public static function homepageCacheKeys(): array
    {
        return ['site_settings_cart'];
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
}

<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasCachedOptions;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use SoftDeletes, CompanyScoped, HasCachedOptions, HasHomepageCache;


    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'logo',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description',
        'status',
    ];
    protected $casts = [
        'id'         => 'integer',
        'meta_keywords' => 'array',
    ];

    protected $hidden = ['deleted_at'];
    public static function homepageCacheKeys(): array
    {
        return ['home_brands', 'brand_list_page','shop_brands']; 
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('r2')->url($this->logo)
            : null;
    }
}

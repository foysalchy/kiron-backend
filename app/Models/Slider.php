<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasHomepageCache;
use App\Traits\HasSaasCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    use SoftDeletes, CompanyScoped, HasHomepageCache, HasSaasCache;


    protected $fillable = [
        'company_id',
        'title',
        'subtitle',
        'description',
        'image',
        'mobile_image',
        'url',
        'placement',
        'status',
    ];


    protected $hidden = ['deleted_at'];
    public static function homepageCacheKeys(): array
    {
        return ['home_sliders'];
    }
    public static function saasCacheKeys(): array
    {
        return ['saas_home_sliders'];
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        if ($this->mobile_image) {
            return Storage::disk('r2')->url($this->mobile_image);
        }
        
        return $this->image ? Storage::disk('r2')->url($this->image) : null;
    }
}

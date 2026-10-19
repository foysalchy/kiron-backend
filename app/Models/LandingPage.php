<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasSlugCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    use SoftDeletes, CompanyScoped,HasSlugCache;

    protected $fillable = [
        'company_id',
        'template_id',
        'product_id',
        'domain',
        'name',
        'title',
        'button_text',
        'short_description',
        'thumbnail',
        'video',
        'description',
        'regular_price',
        'discount_price',
        'slug',
        'pixel',
        'meta_access_token',
        'header_code',
        'phone_number',
        'instruction',
        'extras',
        'instruction_title',
        'img_paths',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
    ];
    protected $casts = [
        'extras' => 'array',
        'img_paths' => 'array',
        'meta_keywords' => 'array',
    ];
    protected $hidden = ['deleted_at', 'meta_access_token'];


    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get multi-products list (falls back to single product)
     */
    public function getProductsAttribute()
    {
        $ids = $this->product_ids;
        if (empty($ids)) {
            return $this->product ? collect([$this->product]) : collect();
        }
        return Product::whereIn('id', $ids)->get();
    }

    /**
     * Get array of product ids
     */
    public function getProductIdsAttribute(): array
    {
        if (!empty($this->extras['product_ids']) && is_array($this->extras['product_ids'])) {
            return array_values(array_filter(array_map('intval', $this->extras['product_ids'])));
        }
        return $this->product_id ? [(int)$this->product_id] : [];
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

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!is_string($this->thumbnail) || empty($this->thumbnail)) {
            return null;
        }
        if (str_starts_with($this->thumbnail, 'data:') || str_starts_with($this->thumbnail, 'blob:') || str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }
        return Storage::disk('r2')->url($this->thumbnail);
    }

    public function getMetaImageUrlAttribute(): ?string
    {
        if (!is_string($this->meta_image) || empty($this->meta_image)) {
            return null;
        }
        if (str_starts_with($this->meta_image, 'data:') || str_starts_with($this->meta_image, 'blob:') || str_starts_with($this->meta_image, 'http')) {
            return $this->meta_image;
        }
        return Storage::disk('r2')->url($this->meta_image);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!is_string($this->video) || empty($this->video)) {
            return null;
        }
        if (str_starts_with($this->video, 'data:') || str_starts_with($this->video, 'blob:')) {
            return $this->video;
        }
        return Storage::disk('r2')->url($this->video);
    }

    // Mutators
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ? Str::slug($value) : null;
    }

    // Boot method to ensure slug uniqueness if provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($landingPage) {
            if (!empty($landingPage->slug)) {
                // Ensure slug uniqueness
                $originalSlug = $landingPage->slug;
                $count = 1;

                while (static::where('slug', $landingPage->slug)->exists()) {
                    $landingPage->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }
    public function getDiscountPercentageAttribute()
    {
        $regular = (float) $this->regular_price;
        $sale = (float) $this->discount_price;

        if ($regular > 0 && $regular > $sale) {
            return round((($regular - $sale) / $regular) * 100);
        }

        return 0;
    }

}

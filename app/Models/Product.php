<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'brand_id',
        'title',
        'slug',
        'thumbnail',
        'video_link',
        'mega_category_ids',
        'sub_category_ids',
        'mini_category_ids',
        'extra_category_ids',
        'short_description',
        'full_description',
        'type',
        'sku_codes',
        'stock_status',
        'stock_quantity',
        'regular_price',
        'discount_type',
        'discount',
        'purpose',
        'status',
    ];

    protected $casts = [
        'mega_category_ids' => 'array',
        'sub_category_ids' => 'array',
        'mini_category_ids' => 'array',
        'extra_category_ids' => 'array',
        'sku_codes' => 'array',
        'stock_quantity' => 'integer',
        'regular_price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    protected $hidden = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);

                // Ensure unique slug
                $count = 1;
                $originalSlug = $product->slug;
                while (static::where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

   
    public function scopeSingleType($query)
    {
        return $query->where('type', 'single');
    }

    public function scopeVariationType($query)
    {
        return $query->where('type', 'variation');
    }

    // Accessors
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function getSalePriceAttribute(): float
    {
        if (!$this->discount || $this->discount <= 0) {
            return $this->regular_price;
        }

        if ($this->discount_type === 'flat') {
            return max(0, $this->regular_price - $this->discount);
        }

        // Percent discount
        $discountAmount = ($this->regular_price * $this->discount) / 100;
        return max(0, $this->regular_price - $discountAmount);
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->discount || $this->discount <= 0) {
            return 0;
        }

        if ($this->discount_type === 'flat') {
            return $this->discount;
        }

        // Percent discount
        return ($this->regular_price * $this->discount) / 100;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock_status === 'in_stock' && $this->stock_quantity > 0;
    }
}

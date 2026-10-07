<?php

namespace App\Models;

use App\Casts\IntegerArray;
use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes, CompanyScoped, HasHomepageCache, HasGlobalLayoutCache;

    protected $fillable = [
        'company_id',
        'brand_id',
        'assigned_to',
        'title',
        'slug',
        'thumbnail',
        'thumbnail_310',
        'thumbnail_95',
        'video_link',
        'mega_category_ids',
        'sub_category_ids',
        'mini_category_ids',
        'extra_category_ids',
        'short_description',
        'full_description',
        'warehouse_info',
        'type',
        'product_type',
        'sku_code',
        'stock_status',
        'stock_quantity',
        'available_stock',
        'regular_price',
        'purchase_price',
        'discount_type',
        'discount',
        'purpose',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'manage_stock',
        'source_info'
    ];

    protected $casts = [
        'id'         => 'integer',
        'mega_category_ids' => IntegerArray::class,
        'sub_category_ids' => IntegerArray::class,
        'mini_category_ids' => IntegerArray::class,
        'extra_category_ids' => IntegerArray::class,
        'warehouse_info' => 'array',
        'source_info' => 'array',
        'sku_code' => 'array',
        'meta_keywords' => 'array',
        'stock_quantity' => 'integer',
        'regular_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'manage_stock' => 'boolean',


    ];

    protected $hidden = ['deleted_at'];
    protected $appends = ['thumbnail_url', 'thumbnail_310_url', 'thumbnail_95_url', 'display_image_url'];

    public function getDisplayImageUrlAttribute(): ?string
    {
        if ($this->type === 'variation') {
            $firstVariation = $this->variations->first();
            if ($firstVariation && $firstVariation->image) {
                return $firstVariation->image_url;
            }
        }

        return $this->thumbnail_url;
    }

    public function getMetaTitleAttribute($value): ?string
    {
        if (empty($value) || in_array(strtolower(trim($value)), ['null', 'undefined'])) {
            return null;
        }
        return $value;
    }

    public function getMetaDescriptionAttribute($value): ?string
    {
        if (empty($value) || in_array(strtolower(trim($value)), ['null', 'undefined'])) {
            return null;
        }
        return $value;
    }

    public static function homepageCacheKeys(): array
    {
        return [
            'home_latest_offers',
            'home_new_arrivals',
            'home_product_groups',
            'home_popular_products',
            'home_all_products',
        ];
    }

    protected static function booted()
    {
        // গ্লোবাল স্কোপ যুক্ত করা হলো যাতে সব জায়গায় ডিফল্টভাবে শুধু 'finished' প্রোডাক্ট আসে
        static::addGlobalScope(new \App\Models\Scopes\FinishedProductScope);

        static::saved(function ($product) {
            \Illuminate\Support\Facades\Cache::forget("product_details_v2_{$product->slug}");
        });

        static::deleted(function ($product) {
            \Illuminate\Support\Facades\Cache::forget("product_details_v2_{$product->slug}");
        });
    }
    public static function globalLayoutSections(): array
    {
        return ['related_products', 'all_header_products'];
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->select('id', 'name');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }


    public function barcode()
    {
        return $this->morphOne(BarCode::class, 'barcodeable');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
    public function productGroups()
    {
        return $this->belongsToMany(ProductGroup::class, 'product_group_product');
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

        return $this->thumbnail
            ? Storage::disk('r2')->url($this->thumbnail)
            : null;
    }

    public function getThumbnail310UrlAttribute(): ?string
    {
        return $this->thumbnail_310
            ? Storage::disk('r2')->url($this->thumbnail_310)
            : null;
    }

    public function getThumbnail95UrlAttribute(): ?string
    {
        return $this->thumbnail_95
            ? Storage::disk('r2')->url($this->thumbnail_95)
            : null;
    }

    public function getSalePriceAttribute()
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
    public function isStockManaged(): bool
    {
        return (bool) $this->manage_stock;
    }
    public function getGroupsAttribute(): array
    {
        $groups = [];

        foreach ($this->variations as $variation) {
            foreach ($variation->attributes as $attr) {
                $group = $attr->group;
                $value = $attr->value;

                if (!isset($groups[$group])) {
                    $groups[$group] = [
                        'name'   => $group,
                        'values' => [],
                    ];
                }

                if (!in_array($value, $groups[$group]['values'])) {
                    $groups[$group]['values'][] = $value;
                }
            }
        }

        return array_values($groups);
    }

    public static function loadCategoriesForCollection($products): Collection
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $megaIds = $products->pluck('mega_category_ids')->flatten()->unique()->filter();
        $subIds = $products->pluck('sub_category_ids')->flatten()->unique()->filter();
        $miniIds = $products->pluck('mini_category_ids')->flatten()->unique()->filter();
        $extraIds = $products->pluck('extra_category_ids')->flatten()->unique()->filter();


        $megaCategories = $megaIds->isNotEmpty()
            ? MegaCategory::whereIn('id', $megaIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $subCategories = $subIds->isNotEmpty()
            ? SubCategory::whereIn('id', $subIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $miniCategories = $miniIds->isNotEmpty()
            ? MiniCategory::whereIn('id', $miniIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $extraCategories = $extraIds->isNotEmpty()
            ? ExtraCategory::whereIn('id', $extraIds)->select('id', 'name', 'slug')->get()->keyBy('id')
            : collect();

        $products->each(function ($product) use ($megaCategories, $subCategories, $miniCategories, $extraCategories) {
            $product->setRelation(
                'mega_categories',
                collect($product->mega_category_ids)->map(fn($id) => $megaCategories->get($id))->filter()->values()
            );

            $product->setRelation(
                'sub_categories',
                collect($product->sub_category_ids)->map(fn($id) => $subCategories->get($id))->filter()->values()
            );

            $product->setRelation(
                'mini_categories',
                collect($product->mini_category_ids)->map(fn($id) => $miniCategories->get($id))->filter()->values()
            );

            $product->setRelation(
                'extra_categories',
                collect($product->extra_category_ids)->map(fn($id) => $extraCategories->get($id))->filter()->values()
            );
        });

        return $products;
    }
    // price variation setup
    public function getDisplayPriceDataAttribute()
    {
        $salePrice = 0;
        $regularPrice = 0;
        $isVariation = ($this->type !== 'single');

        if ($this->type === 'single') {
            $salePrice = $this->sale_price;
            $regularPrice = $this->regular_price;
        } else {
            $firstVar = $this->variations->first();
            if ($firstVar) {
                $salePrice = $firstVar->final_price;
                $regularPrice = $firstVar->regular_price;
            }
        }
        return (object) [
            'sale_price'    => (float) $salePrice,
            'regular_price' => (float) $regularPrice,
            'is_variation'  => $isVariation
        ];
    }

    public function billsOfMaterials(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function activeBom(): BelongsTo
    {
        return $this->belongsTo(BillOfMaterial::class, 'id', 'product_id')->where('status', 'active');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function usedInBoms(): HasMany
    {
        return $this->hasMany(BomItem::class, 'product_id');
    }
}

<?php

namespace App\Models;

use App\Casts\IntegerArray;
use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes, CompanyScoped;

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
        'warehouse_info',
        'type',
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
    ];

    protected $casts = [
        'mega_category_ids' => IntegerArray::class,
        'sub_category_ids' => IntegerArray::class,
        'mini_category_ids' => IntegerArray::class,
        'extra_category_ids' => IntegerArray::class,
        'warehouse_info' => 'array',
        'sku_codes' => 'array',
        'meta_keywords' => 'array',
        'stock_quantity' => 'integer',
        'regular_price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    protected $hidden = ['deleted_at'];



    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->select('id', 'name');
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
        return $this->morphOne(Barcode::class, 'barcodeable');
    }
    // app/Models/Product.php ফাইলে যোগ করুন

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
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
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
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
}

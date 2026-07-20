<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use SoftDeletes, CompanyScoped,HasHomepageCache;

    protected $fillable = [
        'company_id',
        'product_id',
        'variation_id',
        'customer_id',
        'rating',
        'comment',
        'images',
        'status',
    ];

    protected $hidden = ['deleted_at'];
    public static function homepageCacheKeys(): array
    {
        return ['home_reviews'];
    }
    protected $casts = [
        'images' => 'array',
    ];
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

}

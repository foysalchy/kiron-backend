<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use SoftDeletes, CompanyScoped;

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

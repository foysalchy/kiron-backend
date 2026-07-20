<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductGroup extends Model
{
    use SoftDeletes, CompanyScoped, HasHomepageCache;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'filter_type',
        'filter_parameters',
        'product_ids',
        'is_frontend',
        'status',
    ];

    protected $casts = [
        'filter_parameters' => 'array',
        'product_ids'       => 'array',
        'is_frontend'       => 'boolean',
    ];
    public static function homepageCacheKeys(): array
    {
        return ['home_product_groups'];
    }
}

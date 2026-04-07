<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductGroup extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'filter_type',
        'filter_parameters',
        'product_ids',
        'status',
    ];

    protected $casts = [
        'filter_parameters' => 'array',
        'product_ids'       => 'array',
    ];
}

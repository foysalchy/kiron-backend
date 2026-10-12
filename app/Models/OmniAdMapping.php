<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OmniAdMapping extends Model
{
    protected $fillable = [
        'company_id',
        'ad_id',
        'title',
        'product_id',
        'ad_info',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

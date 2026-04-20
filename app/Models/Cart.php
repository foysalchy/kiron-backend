<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use CompanyScoped;

    public const REMOVED = 0;
    public const ADDED = 1;
    public const PURCHASED = 2;
    protected $fillable = [
        'company_id',
        'customer_id',
        'product_id',
        'session_id',
        'variation_id',
        'quantity',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'product_id',
        'customer_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use CompanyScoped;

    protected $fillable = ['company_id', 'product_id', 'customer_id', 'ip_address', 'user_agent'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

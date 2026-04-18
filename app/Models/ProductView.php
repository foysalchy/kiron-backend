<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    use CompanyScoped;

    protected $fillable = ['company_id', 'product_id', 'customer_id', 'ip_address', 'user_agent'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }
}

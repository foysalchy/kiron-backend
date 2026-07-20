<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use Illuminate\Database\Eloquent\Model;

class SearchProduct extends Model
{
    use CompanyScoped, HasGlobalLayoutCache;

    protected $fillable = [
        'company_id',
        'customer_id',
        'keyword',
        'status',
    ];


    protected $hidden = ['deleted_at'];
    public static function globalLayoutSections(): array
    {
        return ['popular_searches'];
    }
    public function customer()
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }
}

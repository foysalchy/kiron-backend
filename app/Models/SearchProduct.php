<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class SearchProduct extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'customer_id',
        'keyword',
        'status',
    ];


    protected $hidden = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }
}

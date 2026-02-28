<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use CompanyScoped;
    protected $fillable = ['company_id', 'period_id', 'name', 'type', 'amount', 'is_active'];

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}

<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class BarCode extends Model
{
    use CompanyScoped;
    protected $table = 'bar_codes';
    protected $fillable = ['company_id', 'code'];

    public function barcodeable()
    {
        return $this->morphTo();
    }
}

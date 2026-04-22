<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUpdateRequest extends Model
{
    protected $guarded = ['id'];
     public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}

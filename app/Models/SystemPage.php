<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class SystemPage extends Model


{

    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'page_type',
        'title',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }
}

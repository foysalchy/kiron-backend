<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
     use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'email',
        'status',
    ];

    protected $hidden = ['deleted_at'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }


}

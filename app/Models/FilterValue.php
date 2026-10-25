<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasCachedOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilterValue extends Model
{
    use SoftDeletes, CompanyScoped, HasCachedOptions;

    protected $fillable = [
        'company_id',
        'filter_id',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
}

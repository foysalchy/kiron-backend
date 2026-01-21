<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'category',
        'status',
    ];



    protected $hidden = [
        'deleted_at',
    ];
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
 
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }

    public function scopeByCategory($query, string $cat)
    {
        return $query->where('category', $cat);
    }

}

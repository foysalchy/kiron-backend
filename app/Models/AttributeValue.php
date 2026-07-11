<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasCachedOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeValue extends Model
{
    use SoftDeletes, CompanyScoped,HasCachedOptions;

    protected $fillable = [
        'company_id',
        'attribute_group_id',
        'name',
        'status',
    ];



    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
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
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByGroup($query, int $groupId)
    {
        return $query->where('attribute_group_id', $groupId);
    }
}

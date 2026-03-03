<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDisposal extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'disposal_type_id',
        'date',
        'amount',
        'note',
        'status',
    ];
    protected $hidden = ['deleted_at'];

     //company scope
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }
    public function scopeDisposed($query)
    {
        return $query->where('status', Status::Disposed->value);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function disposalType(): BelongsTo
    {
        return $this->belongsTo(DisposalType::class);
    }
}

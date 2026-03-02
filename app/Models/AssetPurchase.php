<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetPurchase extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'asset_id',
        'supplier_id',
        'purchase_date',
        'purchase_cost',
        'invoice_number',
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

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class,'asset_id');
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'supplier_id');
    }

}

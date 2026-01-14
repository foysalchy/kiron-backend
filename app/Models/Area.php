<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'name',
        'status',
    ];



    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(related: Warehouse::class);
    }
    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class);
    }
}

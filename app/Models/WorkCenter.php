<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'location',
        'machine_name',
        'capacity_per_day',
        'hourly_cost',
        'operating_hours_per_day',
        'responsible_person_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'capacity_per_day' => 'decimal:2',
        'hourly_cost' => 'decimal:2',
        'operating_hours_per_day' => 'decimal:2',
    ];

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProductionStage::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }
}

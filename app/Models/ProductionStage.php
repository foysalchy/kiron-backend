<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionStage extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'sequence',
        'work_center_id',
        'estimated_duration_minutes',
        'assigned_team_or_person',
        'description',
        'status',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'estimated_duration_minutes' => 'integer',
    ];

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(ProductionStageLog::class);
    }
}

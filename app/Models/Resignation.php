<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resignation extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'letter',
        'type',
        'resign_rule_ids',
        'letter_received_date',
        'resign_date',
        'reason',
        'activities',
        'is_applied',
        'status'
    ];
    protected $casts = [
        'resign_rule_ids'      => 'array',
        'is_applied'           => 'boolean',
        'letter_received_date' => 'date',
        'resign_date'          => 'date',
    ];

    protected $hidden = ['deleted_at'];
    protected $appends = ['resign_rules'];
    //scopes
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function getResignRulesAttribute()
    {
        return ResignRule::whereIn('id', $this->resign_rule_ids ?? [])->get(['id', 'name']);
    }
    public function getLetterUrlAttribute(): ?string
    {
        return $this->letter ? asset('storage/' . $this->letter) : null;
    }
}

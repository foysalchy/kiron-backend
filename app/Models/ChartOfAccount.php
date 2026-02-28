<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'account_group_id',
        'name',
        'description',
        'status',
        ];
    protected $hidden = ['deleted_at'];
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class);
    }
    public function recurringJournal(): HasMany
    {
        return $this->hasMany(RecurringJournal::class);
    }

    //company scope
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }

}

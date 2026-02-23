<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'account_type_id',
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
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }
    public function chartOfAccount(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class);
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

<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringJournal extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'created_by',
        'from_account_id',
        'to_account_id',
        'start_date',
        'amount',
        'repeat_interval',
        'interval_type',
        'last_transaction_date',
        'description',
        'approval_status',
        'operational_status',
    ];protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }
     //company scope
    public function scopeDraft($query)
    {
        return $query->where('status', Status::Draft->value);
    }
    public function scopeApproved($query)
    {
        return $query->where('status', Status::Approved->value);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }
}

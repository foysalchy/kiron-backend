<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionTransfer extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'from_account_id',
        'date',
        'total_amount',
        'reference_number',
        'file',
        'description',
        'status',
        'created_by'
    ];
    protected $hidden = ['deleted_at'];
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionTransferDetails::class, 'transfer_id');
    }public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //company scope
    public function scopeDraft($query)
    {
        return $query->where('status', Status::Draft->value);
    }
    public function scopeCancelled($query)
    {
        return $query->where('status', Status::Cancelled->value);
    }
    public function scopeApproved($query)
    {
        return $query->where('status', Status::Approved->value);
    }
    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }
    //for reference number
    public function generateReferenceNumber(): void
    {
        if ($this->reference_number) {
            return;
        }

        $year = date('Y');
        $prefix = "TRF-{$year}-";

        // Table name is transaction_transfers
        $latest = self::withoutGlobalScopes()
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(reference_number, -8) AS UNSIGNED) DESC')
            ->first();

        $sequence = $latest ? ((int) substr($latest->reference_number, -8)) + 1 : 1;

        $this->reference_number = $prefix . str_pad($sequence, 8, '0', STR_PAD_LEFT);
        $this->save();
    }
}

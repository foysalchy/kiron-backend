<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionJournal extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'created_by',
        'reference_number',
        'voucher_type',
        'voucher_no',
        'source_type',
        'source_id',
        'party_id',
        'date',
        'description',
        'narration',
        'file',
        'total_debit',
        'total_credit',
        'status',
    ];
    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function accounts(): HasMany
    {
        return $this->hasMany(TransactionJournalAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
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
    // Reference Number Generation
    public function generateReferenceNumber(): void
    {
        if ($this->reference_number) return;

        $year = date('Y');
        $prefix = "JV-{$year}-"; // Journal Voucher

        $latest = self::withoutGlobalScopes()
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(reference_number, -8) AS UNSIGNED) DESC')
            ->first();

        $sequence = $latest ? ((int) substr($latest->reference_number, -8)) + 1 : 1;

        $this->reference_number = $prefix . str_pad($sequence, 8, '0', STR_PAD_LEFT);
        $this->save();
    }
}

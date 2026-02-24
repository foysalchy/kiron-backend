<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionExpense extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'expense_from_id',
        'reference_number',
        'date',
        'description',
        'file',
        'total_amount',
        'status',
        'created_by',
    ];
    protected $hidden = ['deleted_at'];
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function categories(): HasMany
    {
        return $this->hasMany(TransactionExpenseCategory::class, 'expense_id');
    }

    public function expenseFrom(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_from_id');
    }

    public function creator(): BelongsTo
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
    /**
     * Generate Reference only when Approved
     */
    public function generateReferenceNumber(): void
    {
        if ($this->reference_number) return;

        $year = date('Y');
        $prefix = "DV-{$year}-";

        $latest = self::withoutGlobalScopes()
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(reference_number, -8) AS UNSIGNED) DESC')
            ->first();

        $sequence = $latest ? ((int)substr($latest->reference_number, -8)) + 1 : 1;
        $this->reference_number = $prefix . str_pad($sequence, 8, '0', STR_PAD_LEFT);
        $this->save();
    }
    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }

}

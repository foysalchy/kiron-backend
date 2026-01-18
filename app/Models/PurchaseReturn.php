<?php

namespace App\Models;

use App\Traits\CompanyScoped;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class PurchaseReturn extends Model
{
    use CompanyScoped, SoftDeletes;

    // Status constants
    public const STATUS_DRAFT = 0;
    public const STATUS_COMPLETED = 1;
    public const STATUS_CANCELLED = 2;

    protected $fillable = [
        'company_id',
        'purchase_id',
        'supplier_id',
        'return_no',
        'return_date',
        'total_quantities',
        'grand_total',
        'status',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_quantities' => 'integer',
        'grand_total' => 'decimal:2',
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_no)) {
                $return->return_no = self::generateReturnNumber();
            }
        });
    }


    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'supplier_id')->select('id','name','type');
    }

    public function purchaseReturnDetails(): HasMany
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }

    /**
     * Status helper methods
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RTN';
        $date = now()->format('Ymd');

        // Get last return number for today
        $lastReturn = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReturn) {
            // Extract last 4 digits
            $lastNumber = (int) substr($lastReturn->return_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        // Example: RTN-20260117-0001
    }
}

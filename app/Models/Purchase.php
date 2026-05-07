<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes, CompanyScoped;

    const PAYMENT_UNPAID = 0;
    const PAYMENT_PARTIAL = 1;
    const PAYMENT_PAID = 2;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'supplier_id',
        'requisition_id',
        'reference_no',
        'purchase_date',
        'due_date',
        'total_quantities',
        'subtotal',
        'other_charges',
        'discount_on_all',
        'round_off',
        'grand_total',
        'payment_amount',
        'payment_type',
        'account',
        'payment_note',
        'status',
        'payment_status',
        'note',
    ];

    protected $casts = [
       
        'total_quantities' => 'integer',
        'subtotal' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'discount_on_all' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'status' => 'integer',
        'payment_status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->reference_no = self::generateReferenceNumber();
        });
    }

    public static function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPurchase = self::whereDate('created_at', now())
            ->latest('id')
            ->first();

        $number = $lastPurchase ? (int) substr($lastPurchase->reference_no, -4) + 1 : 1;

        return 'PUR-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()->sum('amount');

        if ($totalPaid <= 0) {
            $status =   self::PAYMENT_UNPAID; // unpaid
        } elseif ($totalPaid >= $this->grand_total) {
            $status =   self::PAYMENT_PAID ; // paid
        } else {
            $status = self::PAYMENT_PARTIAL; // partial
        }

        $this->update(['payment_status' => $status]);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class)->select('id', 'name');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'supplier_id')->select('id', 'name');
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    // Scopes
    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeBySupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, int $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::Completed->value);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', Status::Draft->value);
    }


    public function getPaymentStatusTextAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PARTIAL => 'Partial',
            self::PAYMENT_PAID => 'Paid',
            default => 'Unknown',
        };
    }

    public function getDueAmountAttribute(): float
    {
        $paid = $this->payment_amount ?? 0;
        return max(0, $this->grand_total - $paid);
    }

    // Helper Methods
    public function isDraft(): bool
    {
        return $this->status === Status::Draft->value;
    }

    public function isCompleted(): bool
    {
        return $this->status === Status::Completed->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }
}

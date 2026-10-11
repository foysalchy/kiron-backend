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
        'other_charges_details',
        'discount_on_all',
        'round_off',
        'grand_total',
        'status',
        'payment_status',
        'note',
    ];

    protected $casts = [
       
        'total_quantities' => 'integer',
        'subtotal' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'other_charges_details' => 'array',
        'discount_on_all' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => 'integer',
        'payment_status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_no)) {
                $model->reference_no = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'PUR-' . $date . '-';

        // Check across all purchases including soft-deleted ones and other companies/global scopes
        $lastPurchase = self::withoutGlobalScopes()
            ->withTrashed()
            ->where('reference_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;
        if ($lastPurchase && preg_match('/-(\d+)$/', $lastPurchase->reference_no, $matches)) {
            $number = (int) $matches[1] + 1;
        }

        $refNo = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

        // Ensure absolute uniqueness in database
        while (self::withoutGlobalScopes()->withTrashed()->where('reference_no', $refNo)->exists()) {
            $number++;
            $refNo = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        }

        return $refNo;
    }
    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $supplierBill = max(0, (float) $this->grand_total - (float) ($this->other_charges ?? 0));

        if ($totalPaid <= 0) {
            $status = self::PAYMENT_UNPAID; // unpaid
        } elseif ($totalPaid >= $supplierBill) {
            $status = self::PAYMENT_PAID; // paid
        } else {
            $status = self::PAYMENT_PARTIAL; // partial
        }

        $this->update([
            'payment_status' => $status,
        ]);
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
        $paid = (float) $this->payments()->sum('amount');
        $supplierBill = max(0, (float) $this->grand_total - (float) ($this->other_charges ?? 0));
        return max(0, $supplierBill - $paid);
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

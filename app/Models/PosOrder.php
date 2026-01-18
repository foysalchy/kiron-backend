<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class PosOrder extends Model
{
    use HasFactory, SoftDeletes,CompanyScoped;

    // Status constants
    public const STATUS_PENDING = 0;
    public const STATUS_COMPLETED = 1;
    public const STATUS_CANCELLED = 2;
    public const STATUS_ON_HOLD = 3;

    // Payment status constants
    public const PAYMENT_PENDING = 0;
    public const PAYMENT_PARTIAL = 1;
    public const PAYMENT_PAID = 2;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'customer_id',
        'coupon_id',
        'order_no',
        'order_date',
        'is_walk_in',
        'total_items',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_status',
        'status',
        'note',
        'hold_ref',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'is_walk_in' => 'boolean',
        'total_items' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_status' => 'integer',
        'status' => 'integer',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_no)) {
                $order->order_no = self::generateOrderNumber();
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function posOrderDetails(): HasMany
    {
        return $this->hasMany(PosOrderDetail::class);
    }

    public function posOrderPayments(): HasMany
    {
        return $this->hasMany(PosOrderPayment::class);
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    /**
     * Payment status helpers
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function isPaymentPartial(): bool
    {
        return $this->payment_status === self::PAYMENT_PARTIAL;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ON_HOLD => 'On Hold',
            default => 'Unknown',
        };
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PARTIAL => 'Partial',
            self::PAYMENT_PAID => 'Paid',
            default => 'Unknown',
        };
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }
    public static function generateOrderNumber(): string
    {
        $prefix = 'POS';
        $date = now()->format('Ymd');

        // Get last order number for today
        $lastOrder = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            // Extract last 4 digits
            $lastNumber = (int) substr($lastOrder->order_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        // Example: POS-20260117-0001
    }
}

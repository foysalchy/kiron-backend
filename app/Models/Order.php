<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory, SoftDeletes, CompanyScoped;

    // Order types
    public const TYPE_POS = 'pos';
    public const TYPE_SALES = 'sales';
    // Payment status constants
    public const PAYMENT_UNPAID = 0;
    public const PAYMENT_PARTIAL = 1;
    public const PAYMENT_PAID = 2;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'customer_id',
        'coupon_id',
        'type',
        'order_no',
        'reference_no',
        'order_date',
        'is_walk_in',
        'total_quantities',
        'subtotal',
        'other_charges',
        'discount_on_all',
        'coupon_discount',
        'round_off',
        'grand_total',
        'payment_amount',
        'payment_status',
        'status',
        'note',
        'hold_ref',
    ];

    protected $casts = [
        'order_date' => 'date',
        'is_walk_in' => 'boolean',
        'total_quantities' => 'integer',
        'subtotal' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'discount_on_all' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'payment_status' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Boot method for auto-generating order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_no)) {
                $order->order_no = self::generateOrderNumber($order->type);
            }
        });
    }

    /**
     * Generate order number based on type
     */
    public static function generateOrderNumber(string $type): string
    {
        $prefix = $type === self::TYPE_POS ? 'POS' : 'SALE';
        $date = now()->format('Ymd');

        // Get last order number for today and this type
        $lastOrder = self::where('type', $type)
            ->whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        // Example: POS-20260117-0001 or SALE-20260117-0001
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

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }
    /**
     * Type helpers
     */
    public function isPOS(): bool
    {
        return $this->type === self::TYPE_POS;
    }

    public function isSales(): bool
    {
        return $this->type === self::TYPE_SALES;
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === Status::Pending->value;
    }

    public function isCompleted(): bool
    {
        return $this->status === Status::Completed->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }

    public function isOnHold(): bool
    {
        return $this->status === Status::Hold->value;
    }

    /**
     * Payment status helpers
     */
    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_UNPAID;
    }

    public function isPartialPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PARTIAL;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include orders with a given payment status
     */
    public function scopePaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope a query to filter by customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_POS => 'POS Order',
            self::TYPE_SALES => 'Sales Order',
            default => 'Unknown',
        };
    }



    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PARTIAL => 'Partial',
            self::PAYMENT_PAID => 'Paid',
            default => 'Unknown',
        };
    }

    /**
     * Scopes
     */
    public function scopePOS($query)
    {
        return $query->where('type', self::TYPE_POS);
    }

    public function scopeSales($query)
    {
        return $query->where('type', self::TYPE_SALES);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::Pending->value);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::Completed->value);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', Status::Hold->value);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }
}

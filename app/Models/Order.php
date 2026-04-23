<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasPackageLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory, SoftDeletes, CompanyScoped, HasPackageLimits;
    public string $limitKey = 'order';

    // Order types
    public const TYPE_POS = 'pos';
    public const TYPE_SALES = 'sales';
    // Payment status constants
    public const PAYMENT_UNPAID = 0;
    public const PAYMENT_PARTIAL = 1;
    public const PAYMENT_PAID = 2;
    public const PAYMENT_PENDING = 3;

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
        'shipping_address',
        'payment_amount',
        'payment_status',
        'courier_info',
        'status',
        'note',
        'hold_ref',
        'return_info',
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
        'courier_info' => 'array',
        'shipping_address' => 'array',
        'return_info' => 'array',
    ];

    /**
     * Boot method for auto-generating order number
     */
    protected static function boot()
    {
        parent::boot();

        // Order number generation
        static::creating(function ($order) {
            if (empty($order->order_no)) {
                $order->order_no = self::generateOrderNumber($order->type);
            }
        });

        // Extra charge record
        static::created(function ($order) {
            if ($order->type !== self::TYPE_SALES) return;

            $company = \App\Models\Company::with('pricingPackage')
                ->find($order->company_id);

            if (!$company?->pricingPackage) return;

            $package = $company->pricingPackage;

            if ($package->order_limit === null)        return;
            if ($package->extra_order_charge === null) return;

            $monthlyCount = self::withoutGlobalScope('company')
                ->where('company_id', $order->company_id)
                ->where('type', self::TYPE_SALES)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($monthlyCount > $package->order_limit) {
                ExtraOrderCharge::create([
                    'company_id'    => $order->company_id,
                    'order_id'      => $order->id,
                    'charge_amount' => $package->extra_order_charge,
                    'month'         => now()->format('Y-m'),
                ]);
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
        $lastOrder = self::withoutGlobalScopes()
            ->withTrashed()
            ->where('type', $type)
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
    public function actionLogs(): HasMany
    {
        return $this->hasMany(ActionLog::class, 'action_id')
            ->where('module', 'orders')
            ->orderBy('created_at', 'desc');
    }


    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'asc');
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
    public function isDraft(): bool
    {
        return $this->status === Status::Draft->value;
    }

    public function isDelivered(): bool
    {
        return $this->status === Status::Delivered->value;
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
    public function isPendingPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }
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
            self::PAYMENT_PENDING => 'Pending',
            default => 'Unknown',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID    => 'text-green-600',
            self::PAYMENT_PARTIAL => 'text-blue-600',
            self::PAYMENT_UNPAID  => 'text-red-600',
            self::PAYMENT_PENDING => 'text-yellow-600',
            default               => 'text-red-600',
        };
    }
    public function getStatusLabelAttribute(): string
    {
        // Uses the label() method you defined in your Status Enum
        return Status::from($this->status)->label();
    }

    /**
     * 2. Get Order Status Color (Tailwind classes)
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            Status::Pending->value    => 'bg-orange-100 text-orange-700',
            Status::Processing->value => 'bg-blue-100 text-blue-700',
            Status::Delivered->value  => 'bg-green-100 text-green-700',
            Status::Cancelled->value  => 'bg-red-100 text-red-700',
            Status::ReturnRequest->value => 'bg-purple-100 text-purple-700',
            default                       => 'bg-gray-100 text-gray-700',
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

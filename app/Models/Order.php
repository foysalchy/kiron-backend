<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasPackageLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes, CompanyScoped, HasPackageLimits;
    public string $limitKey = 'order';

    // Order types
    public const TYPE_POS = 'pos';
    public const TYPE_SALES = 'sales';
    public const TYPE_LANDING = 'landing';
    // Payment status constants
    public const PAYMENT_UNPAID = 0;
    public const PAYMENT_PARTIAL = 1;
    public const PAYMENT_PAID = 2;
    public const PAYMENT_PENDING = 3;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'warehouse_info',
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
        'assigned_to',
        'status',
        'note',
        'hold_ref',
        'return_info',
        'source_info',
        'pixel_source_info',
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
        'courier_info' => 'array',
        'shipping_address' => 'array',
        'return_info' => 'array',
        'warehouse_info' => 'array',
        'assigned_to' => 'array',
        'source_info' => 'array',
        'pixel_source_info' => 'array',
        


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
                // ✅ ekta application-level lock, jate ekshathe dui request 
                // number generate korte na pare
                $lock = Cache::lock('order-number-generation-' . $order->type, 10);

                $lock->block(5, function () use ($order) {
                    $order->order_no = self::generateOrderNumber($order->type);
                });
            }
        });
        // Extra charge record
        static::created(function ($order) {
      

            $company = \App\Models\Company::with('pricingPackage')
                ->find($order->company_id);

            if (!$company?->pricingPackage) return;

            $package = $company->pricingPackage;

            if ($package->order_limit === null)        return;
            if ($package->extra_order_charge === null) return;

            DB::transaction(function () use ($order, $package) {
                $yearMonth = now()->format('Y-m');

                // lockForUpdate দিয়ে row lock করবো, race condition (concurrent order) থেকে বাঁচার জন্য
                $usage = CompanyMonthlyUsage::withoutGlobalScope('company')
                    ->where('company_id', $order->company_id)
                    ->where('year_month', $yearMonth)
                    ->lockForUpdate()
                    ->first();

                if (!$usage) {
                    $usage = CompanyMonthlyUsage::create([
                        'company_id'  => $order->company_id,
                        'year_month'  => $yearMonth,
                        'order_count' => 0,
                    ]);
                }

                $usage->increment('order_count');



                if ($usage->order_count > $package->order_limit) {
                    ExtraOrderCharge::firstOrCreate(
                        ['order_id' => $order->id], // duplicate charge protect korbe
                        [
                            'company_id'    => $order->company_id,
                            'charge_amount' => $package->extra_order_charge,
                            'month'         => $yearMonth,
                        ]
                    );
                }
            });
        });
    }

    /**
     * Generate order number based on type
     */
    public static function generateOrderNumber(string $type): string
    {
        $prefix = match ($type) {
            self::TYPE_POS => 'POS',
            self::TYPE_LANDING => 'LAND',
            default => 'SALE',
        };
        $date = now()->format('Ymd');

        $lastOrder = self::withoutGlobalScopes()
            ->withTrashed()
            ->where('type', $type)
            ->whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $newNumber = $lastOrder
            ? ((int) substr($lastOrder->order_no, -4)) + 1
            : 1;

        $orderNo = $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        while (self::withoutGlobalScopes()->withTrashed()->where('order_no', $orderNo)->exists()) {
            $newNumber++;
            $orderNo = $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        return $orderNo;
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
    public function isLanding(): bool
    {
        return $this->type === self::TYPE_LANDING;
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === Status::Pending;
    }
    public function isDraft(): bool
    {
        return $this->status === Status::Draft;
    }

    public function isDelivered(): bool
    {
        return $this->status === Status::Delivered;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled;
    }

    public function isOnHold(): bool
    {
        return $this->status === Status::Hold;
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
            self::TYPE_LANDING => 'Landing Page Order',
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
        // return Status::from($this->status)->label();
        // return $this->status?->label() ?? 'Unknown';
        $statusEnum = Status::tryFrom($this->status);

        return $statusEnum ? $statusEnum->label() : 'Unknown';
    }

    /**
     * 2. Get Order Status Color (Tailwind classes)
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            Status::Pending    => 'bg-orange-100 text-orange-700',
            Status::Processing => 'bg-blue-100 text-blue-700',
            Status::Delivered  => 'bg-green-100 text-green-700',
            Status::Cancelled  => 'bg-red-100 text-red-700',
            Status::ReturnRequest => 'bg-purple-100 text-purple-700',
            Status::Draft      => 'bg-gray-100 text-gray-700',
            default            => 'bg-gray-100 text-gray-700',
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
    public function scopeLanding($query)
    {
        return $query->where('type', self::TYPE_LANDING);
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

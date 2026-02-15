<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class OrderReturn extends Model
{
    use HasFactory, SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'customer_id',
        'order_id',
        'return_no',
        'return_date',
        'reason',
        'total_quantities',
        'subtotal',
        'other_charges',
        'discount_on_all',
        'coupon_discount',
        'round_off',
        'grand_total',
        'refund_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_quantities' => 'integer',
        'subtotal' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'discount_on_all' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'refund_amount' => 'decimal:2',

    ];

    /**
     * Boot method for auto-generating return number
     */
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
     * Generate return number
     */
    public static function generateReturnNumber(): string
    {
        $prefix = 'RTN';
        $date = now()->format('Ymd');

        $lastReturn = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReturn) {
            $lastNumber = (int) substr($lastReturn->return_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        // Example: RTN-20260117-0001
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderReturnDetails(): HasMany
    {
        return $this->hasMany(OrderReturnDetail::class);
    }

    public function orderReturnPayments(): HasMany
    {
        return $this->hasMany(OrderReturnPayment::class);
    }
    public function actionLogs(): HasMany
    {
        return $this->hasMany(ActionLog::class, 'action_id')
            ->where('module', 'order_return')
            ->orderBy('created_at', 'desc');
    }
    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === Status::Pending->value;
    }

    public function isCleared(): bool
    {
        return $this->status === Status::Cleared->value;
    }

    public function isNotCleared(): bool
    {
        return $this->status === Status::NotCleared->value;
    }

    public function isWaiting(): bool
    {
        return $this->status === Status::Waiting->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }



    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', Status::Pending->value);
    }

    public function scopeCleared($query)
    {
        return $query->where('status', Status::Cleared->value);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', Status::Waiting->value);
    }
}

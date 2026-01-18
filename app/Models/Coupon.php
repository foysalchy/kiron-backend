<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes,CompanyScoped;

    // Discount type constants
    public const DISCOUNT_FIXED = 1;
    public const DISCOUNT_PERCENTAGE = 2;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_purchase_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'used_count',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_type' => 'integer',
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if coupon is valid (active, within date range, usage limit not exceeded)
     */
    public function isValid(): bool
    {
        $now = Carbon::now();

        // Check status
        if (!$this->isActive()) {
            return false;
        }

        // Check date range
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if customer can use this coupon
     */
    public function canBeUsedByCustomer(?int $customerId): bool
    {
        if (!$customerId || !$this->usage_limit_per_customer) {
            return true;
        }

        $usageCount = $this->couponUsages()
            ->where('customer_id', $customerId)
            ->count();

        return $usageCount < $this->usage_limit_per_customer;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->discount_type === self::DISCOUNT_FIXED) {
            return min($this->discount_value, $orderAmount);
        }

        // Percentage discount
        $discount = ($orderAmount * $this->discount_value) / 100;

        if ($this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return round($discount, 2);
    }

    /**
     * Check if order amount meets minimum purchase requirement
     */
    public function meetsMinimumPurchase(float $orderAmount): bool
    {
        return $orderAmount >= $this->min_purchase_amount;
    }

    /**
     * Get discount type label
     */
    public function getDiscountTypeLabelAttribute(): string
    {
        return match($this->discount_type) {
            self::DISCOUNT_FIXED => 'Fixed',
            self::DISCOUNT_PERCENTAGE => 'Percentage',
            default => 'Unknown',
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });
    }
}
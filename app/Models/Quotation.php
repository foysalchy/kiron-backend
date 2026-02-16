<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Quotation extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'quotation_no',
        'warehouse_id',
        'quotation_date',
        'name',
        'phone',
        'address',
        'valid_until',
        'reference_no',
        'total_items',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_charges',
        'other_charges',
        'grand_total',
        'status',
        'terms_conditions',
        'note',
        'internal_note',
        'converted_to_order_id',
        'converted_at',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'total_items' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => 'integer',
        'converted_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->quotation_no = self::generateQuotationNo();
        });
    }

    /**
     * Generate unique quotation number
     */

    public static function generateQuotationNo(): string
    {
        $date = now()->format('Ymd');
        $lastQuotation = self::whereDate('created_at', now())
            ->latest('id')
            ->first();

        $number = $lastQuotation ? (int) substr($lastQuotation->quotation_no, -4) + 1 : 1;

        return 'QT-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_to_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Status helpers
     */
    public function isDraft(): bool
    {
        return $this->status === Status::Draft->value;
    }

    public function isSent(): bool
    {
        return $this->status === Status::Sent->value;
    }

    public function isAccepted(): bool
    {
        return $this->status === Status::Accepted->value;
    }

    public function isRejected(): bool
    {
        return $this->status === Status::Rejected->value;
    }

    public function isExpired(): bool
    {
        return $this->status === Status::Expired->value;
    }

    public function isConverted(): bool
    {
        return !empty($this->converted_to_order_id);
    }

    /**
     * Check if quotation is valid (not expired)
     */
    public function isValid(): bool
    {
        if (!$this->valid_until) {
            return true;
        }

        return now()->lte($this->valid_until);
    }




    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', Status::Draft->value);
    }

    public function scopeSent($query)
    {
        return $query->where('status', Status::Sent->value);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', Status::Accepted->value);
    }

    public function scopeNotConverted($query)
    {
        return $query->whereNull('converted_to_order_id');
    }
}

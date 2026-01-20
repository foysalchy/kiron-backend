<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StockMovementRequest extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'request_number',
        'request_date',
        'source_warehouse_id',
        'destination_warehouse_id',
        'notes',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 3;
    const STATUS_TRANSFERRED = 2;
    const STATUS_CANCELLED = 4;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->request_number = self::generateRequestNumber();
            $model->requested_by =Auth::id();
  
        });
    }

    public static function generateRequestNumber(): string
    {
        $date = now()->format('Ymd');
        $lastRequest = self::whereDate('created_at', now())
            ->latest('id')
            ->first();

        $number = $lastRequest ? (int) substr($lastRequest->request_number, -4) + 1 : 1;

        return 'SMR-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockMovementRequestItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function stockMovement()
    {
        return $this->hasOne(StockMovement::class, 'request_id');
    }

    // Status Check Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isTransferred(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
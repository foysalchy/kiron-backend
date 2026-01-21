<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'movement_number',
        'movement_date',
        'source_warehouse_id',
        'destination_warehouse_id',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->movement_number = self::generateMovementNumber();
            $model->created_by = Auth::id();
        });
    }

    public static function generateMovementNumber()
    {
        $date = now()->format('Ymd');
        $lastMovement = self::whereDate('created_at', now())
            ->latest('id')
            ->first();

        $number = $lastMovement ? (int) substr($lastMovement->movement_number, -4) + 1 : 1;

        return 'SM-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === Status::Pending->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }

    public function isApproved(): bool
    {
        return $this->status === Status::Approved->value;
    }
}


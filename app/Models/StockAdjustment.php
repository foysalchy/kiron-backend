<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StockAdjustment extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'adjustment_number',
        'adjustment_date',
        'warehouse_id',
        'adjustment_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    // Adjustment Reasons
    const REASON_DAMAGE = 'damage';
    const REASON_LOSS = 'loss';
    const REASON_FOUND = 'found';
    const REASON_CORRECTION = 'correction';
    const REASON_THEFT = 'theft';
    const REASON_EXPIRED = 'expired';
    const REASON_RETURN = 'return';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->adjustment_number = self::generateAdjustmentNumber();
            $model->created_by = Auth::id();
        });
    }

    public static function generateAdjustmentNumber(): string
    {
        $date = now()->format('Ymd');
        $lastAdjustment = self::whereDate('created_at', now())
            ->latest('id')
            ->first();

        $number = $lastAdjustment ? (int) substr($lastAdjustment->adjustment_number, -4) + 1 : 1;

        return 'ADJ-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function warehouse() : BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items() : HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

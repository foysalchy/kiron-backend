<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class ProductStockLedger extends Model
{


    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bin_id',
        'batch_number',
        'serial_numbers',
        'transaction_type',
        'reference_type',
        'reference_id',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'serial_numbers' => 'array',
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
    ];

    // Transaction Types
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN = 'return';
    const TYPE_INITIAL_STOCK = 'initial_stock';
    const TYPE_CORRECTION = 'correction';

    public const TYPES = [
        self::TYPE_PURCHASE,
        self::TYPE_SALE,
        self::TYPE_TRANSFER_IN,
        self::TYPE_TRANSFER_OUT,
        self::TYPE_ADJUSTMENT,
        self::TYPE_RETURN,
        self::TYPE_INITIAL_STOCK,
        self::TYPE_CORRECTION,
    ];

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bin()
    {
        return $this->belongsTo(Bin::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Polymorphic relationship for reference
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByBin($query, int $binId)
    {
        return $query->where('bin_id', $binId);
    }

    public function scopeByBatch($query, string $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    public function scopeByTransactionType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_PURCHASE,
            self::TYPE_TRANSFER_IN,
            self::TYPE_INITIAL_STOCK
        ]);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_SALE,
            self::TYPE_TRANSFER_OUT
        ]);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Accessor for formatted transaction type
     */
    public function getTransactionTypeNameAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_SALE => 'Sale',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_INITIAL_STOCK => 'Initial Stock',
            default => 'Unknown',
        };
    }

    /**
     * Check if transaction is incoming
     */
    public function isIncoming(): bool
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE,
            self::TYPE_TRANSFER_IN,
            self::TYPE_INITIAL_STOCK
        ]);
    }

    /**
     * Check if transaction is outgoing
     */
    public function isOutgoing(): bool
    {
        return in_array($this->transaction_type, [
            self::TYPE_SALE,
            self::TYPE_TRANSFER_OUT
        ]);
    }

    /**
     * Get current stock for a product in a warehouse
     */
    public static function getCurrentStock(
        int $productId,
        int $warehouseId,
        ?int $binId = null,
        ?string $batchNumber = null
    ): int {
        $query = self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($binId) {
            $query->where('bin_id', $binId);
        }

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        $lastLedger = $query->latest()->first();

        return $lastLedger ? $lastLedger->quantity_after : 0;
    }

    /**
     * Get stock movement history
     */
    public static function getStockHistory(
        int $productId,
        int $warehouseId,
        ?int $binId = null,
        ?string $batchNumber = null,
        ?int $limit = null
    ) {
        $query = self::with(['product', 'warehouse', 'bin', 'creator'])
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($binId) {
            $query->where('bin_id', $binId);
        }

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        $query->orderBy('created_at', 'desc');

        return $limit ? $query->limit($limit)->get() : $query->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariationStockLedger extends Model
{
    protected $fillable = [
        'product_id',
        'variation_id',
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

    /**
     * Get current stock for a variation
     */
    public static function getCurrentStock(
        int $variationId,
        int $warehouseId,
        ?int $binId = null,
        ?string $batchNumber = null
    ): int {
        $query = self::where('variation_id', $variationId)
            ->where('warehouse_id', $warehouseId);

        if ($binId) {
            $query->where('bin_id', $binId);
        }

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        $lastEntry = $query->latest('id')->first();

        return $lastEntry ? $lastEntry->quantity_after : 0;
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bin()
    {
        return $this->belongsTo(Cell::class, 'bin_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

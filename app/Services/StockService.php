<?php

// app/Services/StockService.php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\ProductVariation;
use App\Models\ProductStockLedger;
use App\Models\ProductVariationStockLedger;
use App\Exceptions\InsufficientStockException;

class StockService
{
    /**
     * Order placement এর আগে check - stock available কিনা
     */
    public function isAvailable(ProductVariation $variation, int $qty, int $warehouseId): bool
    {
        if (!$variation->isStockManaged()) {
            return true; // product এর manage_stock false হলে সবসময় available
        }

        $availableQty = $this->getAvailableStock($variation, $warehouseId);

        return $availableQty >= $qty;
    }

    /**
     * Order confirm হলে stock deduct
     */
    public function deduct(ProductVariation $variation, int $qty, int $warehouseId, $orderId): void
    {
        if (!$variation->isStockManaged()) {
            return; // কিছুই deduct হবে না, ledger entry create হবে না
        }

        if (!$this->isAvailable($variation, $qty, $warehouseId)) {
            throw new ApiException(
                "Stock not available for variation #{$variation->id}"
            );
        }

        ProductVariationStockLedger::create([
            'product_variation_id' => $variation->id,
            'warehouse_id' => $warehouseId,
            'order_id' => $orderId,
            'type' => 'deduct',
            'quantity' => -$qty,
        ]);

        ProductStockLedger::create([
            'product_id' => $variation->product_id,
            'warehouse_id' => $warehouseId,
            'order_id' => $orderId,
            'type' => 'deduct',
            'quantity' => -$qty,
        ]);
    }

    /**
     * Order cancel হলে stock reverse
     */
    public function reverse(ProductVariation $variation, int $qty, int $warehouseId, $orderId): void
    {
        if (!$variation->isStockManaged()) {
            return; // deduct হয়নি, reverse ও করার দরকার নাই
        }

        ProductVariationStockLedger::create([
            'product_variation_id' => $variation->id,
            'warehouse_id' => $warehouseId,
            'order_id' => $orderId,
            'type' => 'reverse',
            'quantity' => $qty,
        ]);

        ProductStockLedger::create([
            'product_id' => $variation->product_id,
            'warehouse_id' => $warehouseId,
            'order_id' => $orderId,
            'type' => 'reverse',
            'quantity' => $qty,
        ]);
    }

    /**
     * current available stock বের করা (তোমার existing ledger sum logic)
     */
    public function getAvailableStock(ProductVariation $variation, int $warehouseId): int
    {
        return ProductVariationStockLedger::where('product_variation_id', $variation->id)
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }
}

<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryStockReportService
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function generate(array $filters): array
    {
        try {
            $warehouseId    = $filters['warehouse_id']    ?? null;
            $brandId        = $filters['brand_id']        ?? null;
            $megaCategoryId = isset($filters['mega_category_id']) ? (int) $filters['mega_category_id'] : null;
            $stockFilter    = $filters['stock_filter']    ?? 'all'; // all | low | out
            $startDate      = $filters['start_date']      ?? null;
            $endDate        = $filters['end_date']        ?? null;

            $variationColumn = $this->getVariationColumnName();

            // ── 1. Single Products ──
            $singleQuery = Product::where('type', 'single')
                ->with('brand');

            if ($brandId) {
                $singleQuery->where('brand_id', $brandId);
            }

            if ($megaCategoryId) {
                $singleQuery->whereJsonContains('mega_category_ids', $megaCategoryId);
            }

            $singleProducts = $singleQuery->get()->map(function ($product) use ($warehouseId) {
                $warehouseStocks = collect($product->warehouse_info ?? []);

                if ($warehouseId) {
                    $warehouseStocks = $warehouseStocks->where('warehouse_id', (string) $warehouseId);
                }

                $product->current_stock = $warehouseStocks->sum('quantity');
                return $product;
            });
            $companyId = auth()->user()->company_id;

            // ── 2. Variation Products ──
            $variationQuery = DB::table('product_variation_stocks as pvs')
                ->join('product_variations as pv', "pvs.{$variationColumn}", '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
                ->where('p.company_id', $companyId)
                ->select([
                    'pv.id as variation_id',
                    'pv.product_id',
                    'pv.sku',
                    'pv.regular_price',
                    'pv.purchase_price',
                    'p.title as product_name',
                    'p.mega_category_ids',
                    'p.brand_id',
                    'b.name as brand_name',
                    DB::raw('SUM(pvs.quantity) as current_stock'),
                ])
                ->groupBy(
                    'pv.id',
                    'pv.product_id',
                    'pv.sku',
                    'pv.regular_price',
                    'pv.purchase_price',
                    'p.title',
                    'p.mega_category_ids',
                    'p.brand_id',
                    'b.name'
                );

            if ($warehouseId) {
                $variationQuery->where('pvs.warehouse_id', $warehouseId);
            }

            if ($brandId) {
                $variationQuery->where('p.brand_id', $brandId);
            }

            if ($megaCategoryId) {
                $variationQuery->whereJsonContains('p.mega_category_ids', $megaCategoryId);
            }

            $variationStocks = $variationQuery->get();

            // ── 3. Sold & Return Qty (date range) ──
            $soldQtyMap    = [];
            $returnQtyMap  = [];

            if ($startDate && $endDate) {
                $deliveredStatuses = [Status::Delivered->value];
                $returnedStatuses  = [
                    Status::ReturntoCourier->value,
                    Status::ReturnReceived->value,
                    Status::ReturnRequest->value,
                ];

                $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
                    ->whereIn('status', $deliveredStatuses)
                    ->pluck('id');

                $returnedOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
                    ->whereIn('status', $returnedStatuses)
                    ->pluck('id');

                // Sold
                OrderDetail::whereIn('order_id', $deliveredOrderIds)
                    ->select('product_id', 'variation_id', DB::raw('SUM(quantity) as qty'))
                    ->groupBy('product_id', 'variation_id')
                    ->get()
                    ->each(function ($row) use (&$soldQtyMap) {
                        $key = $row->variation_id
                            ? "v{$row->variation_id}"
                            : "p{$row->product_id}";
                        $soldQtyMap[$key] = (int) $row->qty;
                    });

                // Returned
                OrderDetail::whereIn('order_id', $returnedOrderIds)
                    ->select('product_id', 'variation_id', DB::raw('SUM(quantity) as qty'))
                    ->groupBy('product_id', 'variation_id')
                    ->get()
                    ->each(function ($row) use (&$returnQtyMap) {
                        $key = $row->variation_id
                            ? "v{$row->variation_id}"
                            : "p{$row->product_id}";
                        $returnQtyMap[$key] = (int) $row->qty;
                    });
            }

            // ── 4. Build Product List ──
            $products = [];

            // Single
            foreach ($singleProducts as $product) {
                $key        = "p{$product->id}";
                $stock      = (int) $product->available_stock;
                $soldQty    = $soldQtyMap[$key]   ?? 0;
                $returnQty  = $returnQtyMap[$key] ?? 0;
                $stockValue = $stock * (float) ($product->regular_price ?? 0);

                $row = [
                    'product_id'     => $product->id,
                    'product_name'   => $product->title,
                    'product_type'   => 'single',
                    'variation_id'   => null,
                    'variation_name' => null,
                    'sku'            => $product->sku_code ?? null,
                    'brand'          => $product->brand->name ?? 'No Brand',
                    'current_stock'  => $stock,
                    'sold_qty'       => $soldQty,
                    'return_qty'     => $returnQty,
                    'stock_value'    => round($stockValue, 2),
                    'regular_price'  => (float) ($product->regular_price  ?? 0),
                    'purchase_price' => (float) ($product->purchase_price ?? 0),
                    'is_low_stock'   => $stock > 0 && $stock < self::LOW_STOCK_THRESHOLD,
                    'is_out_of_stock' => $stock <= 0,
                ];

                if ($this->applyStockFilter($row, $stockFilter)) {
                    $products[] = $row;
                }
            }

            // Variation
            $variationGrouped = [];
            foreach ($variationStocks as $vs) {
                $key        = "v{$vs->variation_id}";
                $stock      = (int) $vs->current_stock;
                $soldQty    = $soldQtyMap[$key]   ?? 0;
                $returnQty  = $returnQtyMap[$key] ?? 0;
                $stockValue = $stock * (float) ($vs->regular_price ?? 0);

                $row = [
                    'product_id'     => $vs->product_id,
                    'product_name'   => $vs->product_name,
                    'product_type'   => 'variable',
                    'variation_id'   => $vs->variation_id,
                    'variation_name' => $this->getVariationName($vs->variation_id),
                    'sku'            => $vs->sku ?? null,
                    'brand'          => $vs->brand_name ?? 'No Brand',
                    'current_stock'  => $stock,
                    'sold_qty'       => $soldQty,
                    'return_qty'     => $returnQty,
                    'stock_value'    => round($stockValue, 2),
                    'regular_price'  => (float) ($vs->regular_price  ?? 0),
                    'purchase_price' => (float) ($vs->purchase_price ?? 0),
                    'is_low_stock'   => $stock > 0 && $stock < self::LOW_STOCK_THRESHOLD,
                    'is_out_of_stock' => $stock <= 0,
                ];

                if ($this->applyStockFilter($row, $stockFilter)) {
                    $variationGrouped[$vs->product_id][] = $row;
                }
            }

            // Merge variations into parent
            foreach ($variationGrouped as $productId => $variations) {
                $first = $variations[0];
                $products[] = [
                    'product_id'     => $productId,
                    'product_name'   => $first['product_name'],
                    'product_type'   => 'variable',
                    'variation_id'   => null,
                    'variation_name' => null,
                    'sku'            => null,
                    'brand'          => $first['brand'],
                    'current_stock'  => array_sum(array_column($variations, 'current_stock')),
                    'sold_qty'       => array_sum(array_column($variations, 'sold_qty')),
                    'return_qty'     => array_sum(array_column($variations, 'return_qty')),
                    'stock_value'    => round(array_sum(array_column($variations, 'stock_value')), 2),
                    'regular_price'  => null,
                    'purchase_price' => null,
                    'is_low_stock'   => false,
                    'is_out_of_stock' => false,
                    'variations'     => $variations,
                ];
            }

            // ── 5. Summary ──
            $summary = [
                'total_products'   => count($products),
                'total_stock'      => array_sum(array_column($products, 'current_stock')),
                'total_sold_qty'   => array_sum(array_column($products, 'sold_qty')),
                'total_return_qty' => array_sum(array_column($products, 'return_qty')),
                'total_stock_value' => round(array_sum(array_column($products, 'stock_value')), 2),
                'low_stock_count'  => count(array_filter($products, fn($p) => $p['is_low_stock'])),
                'out_of_stock_count' => count(array_filter($products, fn($p) => $p['is_out_of_stock'])),
                'low_stock_threshold' => self::LOW_STOCK_THRESHOLD,
            ];

            return [
                'date_range' => ['start' => $startDate, 'end' => $endDate],
                'filters'    => [
                    'warehouse_id'    => $warehouseId,
                    'brand_id'        => $brandId,
                    'mega_category_id' => $megaCategoryId,
                    'stock_filter'    => $stockFilter,
                ],
                'summary'  => $summary,
                'products' => $products,
            ];
        } catch (\Exception $e) {
            Log::error('Inventory report failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to generate inventory report');
        }
    }

    private function applyStockFilter(array $row, string $filter): bool
    {
        return match ($filter) {
            'low'  => $row['is_low_stock'],
            'out'  => $row['is_out_of_stock'],
            default => true,
        };
    }

    private function getVariationName(int $variationId): ?string
    {
        $variation = ProductVariation::with('attributes.attributeGroup', 'attributes.attributeValue')
            ->find($variationId);

        if (!$variation?->attributes) return null;

        return $variation->attributes
            ->filter(fn($attr) => $attr->attributeGroup && $attr->attributeValue)
            ->map(fn($attr) => "{$attr->attributeGroup->name}: {$attr->attributeValue->name}")
            ->join(', ');
    }

    private function getVariationColumnName(): string
    {
        return DB::getSchemaBuilder()->hasColumn('product_variation_stocks', 'product_variation_id')
            ? 'product_variation_id'
            : 'variation_id';
    }
}

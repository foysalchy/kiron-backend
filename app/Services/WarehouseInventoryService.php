<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Warehouse, Product, ProductVariation};
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\{DB, Log, Schema};

class WarehouseInventoryService
{
    /**
     * Get the correct variation column name
     */
    private function getVariationColumnName(): string
    {
        // Check which column exists in product_variation_stocks table
        if (Schema::hasColumn('product_variation_stocks', 'product_variation_id')) {
            return 'product_variation_id';
        }
        return 'variation_id'; // fallback
    }

    /**
     * Get warehouse inventory dashboard data
     */
    public function getWarehouseDashboard(): array
    {
        try {
            $warehouses = Warehouse::with(['company'])->get();

            $dashboardData = [];

            foreach ($warehouses as $warehouse) {
                $stats = $this->getWarehouseStats($warehouse->id);

                $dashboardData[] = [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'location' => $warehouse->location,
                    'phone' => $warehouse->phone,
                    'email' => $warehouse->email,
                    'status' => $warehouse->status,
                    'stats' => $stats,
                ];
            }

            return $dashboardData;
        } catch (\Exception $e) {
            Log::error('Error fetching warehouse dashboard: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch warehouse dashboard');
        }
    }

    /**
     * Get detailed stats for a specific warehouse
     */
public function getWarehouseStats(int $warehouseId): array
    {
        $variationColumn = $this->getVariationColumnName();

  
        $singleProducts = Product::where('type', 'single')->get()
            ->map(function ($product) use ($warehouseId) {
                // Get all bins for this specific warehouse
                $warehouseStocks = collect($product->warehouse_info ?? [])
                    ->where('warehouse_id', (string) $warehouseId);
                
                // Sum the quantity across all bins in this warehouse
                $product->warehouse_total_qty = $warehouseStocks->sum('quantity');
                return $product;
            });

        // In Stock Single Products
        $inStockSingle = $singleProducts->where('warehouse_total_qty', '>', 0);
        $singleProductsCount = $inStockSingle->count();
        $singleProductsStock = $inStockSingle->sum('warehouse_total_qty');
        
        // Low Stock Single Products (< 10)
        $lowStockSingleCount = $inStockSingle->where('warehouse_total_qty', '<', 10)->count();

        // Out of Stock Single (Exists in this warehouse record, but qty is 0)
        $outOfStockSingleCount = $singleProducts->filter(function($p) use ($warehouseId) {
            $hasRecord = collect($p->warehouse_info ?? [])->contains('warehouse_id', (string) $warehouseId);
            return $hasRecord && $p->warehouse_total_qty <= 0;
        })->count();

        $singleValue = $inStockSingle->sum(function ($product) {
            return $product->warehouse_total_qty * ($product->regular_price ?? 0);
        });


    
        // Group by variation ID to sum quantities across multiple bins
        $variationStocks = DB::table('product_variation_stocks')
            ->where('warehouse_id', $warehouseId)
            ->select($variationColumn, DB::raw('SUM(quantity) as total_qty'))
            ->groupBy($variationColumn)
            ->get();

        // In Stock Variations
        $inStockVariations = $variationStocks->where('total_qty', '>', 0);
        $variationProductsCount = $inStockVariations->count();
        $variationProductsStock = $inStockVariations->sum('total_qty');

        // Low Stock Variations (< 10)
        $lowStockVariationCount = $inStockVariations->where('total_qty', '<', 10)->count();

        // Out of Stock Variations (Exists in this warehouse record, but qty is 0)
        $outOfStockVariationCount = $variationStocks->where('total_qty', '<=', 0)->count();

        // Calculate Variation Value
        $inStockVariationIds = $inStockVariations->pluck($variationColumn)->toArray();
        $variationPrices = DB::table('product_variations')
            ->whereIn('id', $inStockVariationIds)
            ->pluck('regular_price', 'id');

        $variationValue = $inStockVariations->sum(function ($stock) use ($variationPrices, $variationColumn) {
            $price = $variationPrices[$stock->{$variationColumn}] ?? 0;
            return $stock->total_qty * $price;
        });


        $totalProducts = $singleProductsCount + $variationProductsCount;
        $totalStock = $singleProductsStock + $variationProductsStock;
        $lowStockCount = $lowStockSingleCount + $lowStockVariationCount;
        $outOfStockCount = $outOfStockSingleCount + $outOfStockVariationCount;
        $totalValue = $singleValue + $variationValue;

        // Stock health percentage (Healthy items vs Total items)
        $totalTrackedItems = $totalProducts + $outOfStockCount;
        $stockHealth = $totalTrackedItems > 0 
            ? round((($totalProducts) / $totalTrackedItems) * 100, 1)
            : 0;

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'single_products' => [
                'count' => $singleProductsCount,
                'stock' => $singleProductsStock,
            ],
            'variation_products' => [
                'count' => $variationProductsCount,
                'stock' => $variationProductsStock,
            ],
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'stock_value' => round($totalValue, 2),
            'stock_health' => $stockHealth,
        ];
    }
    /**
     * Get top products by stock in a warehouse
     */
    public function getTopProductsByWarehouse(int $warehouseId, int $limit = 10): array
    {
        $variationColumn = $this->getVariationColumnName();
        $products = [];

        // Get single products
        $singleProducts = Product::where('type', 'single')
            ->with('brand')
            ->get()
            ->filter(function ($product) use ($warehouseId) {
                $stock = collect($product->warehouse_info ?? [])
                    ->firstWhere('warehouse_id', (string) $warehouseId);
                return $stock && ($stock['quantity'] ?? 0) > 0;
            })
            ->map(function ($product) use ($warehouseId) {
                $stock = collect($product->warehouse_info ?? [])
                    ->firstWhere('warehouse_id', (string) $warehouseId);
                return [
                    'id' => $product->id,
                    'name' => $product->title,
                    'sku' => $product->sku,
                    'type' => 'single',
                    'brand' => $product->brand->name ?? 'N/A',
                    'stock' => $stock['quantity'] ?? 0,
                    'value' => ($stock['quantity'] ?? 0) * ($product->regular_price ?? 0),
                ];
            });

        // Get variation products - FIXED with correct column name
        $variationProducts = DB::table('product_variation_stocks')
            ->join('product_variations', "product_variation_stocks.{$variationColumn}", '=', 'product_variations.id')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('product_variation_stocks.warehouse_id', $warehouseId)
            ->where('product_variation_stocks.quantity', '>', 0)
            ->select(
                'product_variations.id',
                'products.title as name',
                'product_variations.sku',
                DB::raw("'variation' as type"),
                'brands.name as brand',
                'product_variation_stocks.quantity as stock',
                DB::raw('product_variation_stocks.quantity * product_variations.regular_price as value')
            )
            ->get()
            ->map(fn($item) => (array) $item);

        // Merge and sort by stock
        $products = collect($singleProducts)
            ->merge($variationProducts)
            ->sortByDesc('stock')
            ->take($limit)
            ->values()
            ->toArray();

        return $products;
    }

    /**
     * Get recent stock movements for a warehouse
     */
    public function getRecentMovements(int $warehouseId, int $limit = 5): array
    {
        $movements = DB::table('stock_movements')
            ->where(function ($q) use ($warehouseId) {
                $q->where('source_warehouse_id', $warehouseId)
                    ->orWhere('destination_warehouse_id', $warehouseId);
            })
            ->where('status', Status::Approved->value)
            ->orderBy('approved_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($movement) use ($warehouseId) {
                $type = $movement->source_warehouse_id == $warehouseId ? 'outgoing' : 'incoming';
                return [
                    'id' => $movement->id,
                    'movement_number' => $movement->movement_number,
                    'type' => $type,
                    'date' => $movement->movement_date,
                    'status' => $movement->status,
                ];
            })
            ->toArray();

        return $movements;
    }
}

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

        // Get single products stock
        $singleProducts = Product::where('type', 'single')
            ->get()
            ->filter(function ($product) use ($warehouseId) {
                $stock = collect($product->warehouse_info ?? [])
                    ->firstWhere('warehouse_id', (string) $warehouseId);
                return $stock && ($stock['quantity'] ?? 0) > 0;
            });

        $singleProductsCount = $singleProducts->count();
        $singleProductsStock = $singleProducts->sum(function ($product) use ($warehouseId) {
            $stock = collect($product->warehouse_info ?? [])
                ->firstWhere('warehouse_id', (string) $warehouseId);
            return $stock['quantity'] ?? 0;
        });

        // Get variation products stock - FIXED with correct column name
        $variationStocks = DB::table('product_variation_stocks')
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->select(
                DB::raw("COUNT(DISTINCT {$variationColumn}) as variation_count"),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->first();

        $variationProductsCount = $variationStocks->variation_count ?? 0;
        $variationProductsStock = $variationStocks->total_quantity ?? 0;

        // Total stats
        $totalProducts = $singleProductsCount + $variationProductsCount;
        $totalStock = $singleProductsStock + $variationProductsStock;

        // Low stock items (stock < 10)
        $lowStockSingle = $singleProducts->filter(function ($product) use ($warehouseId) {
            $stock = collect($product->warehouse_info ?? [])
                ->firstWhere('warehouse_id', (string) $warehouseId);
            $qty = $stock['quantity'] ?? 0;
            return $qty > 0 && $qty < 10;
        })->count();

        $lowStockVariation = DB::table('product_variation_stocks')
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->where('quantity', '<', 10)
            ->count();

        $lowStockCount = $lowStockSingle + $lowStockVariation;

        // Out of stock
        $totalSingleProducts = Product::where('type', 'single')->count();
        $totalVariations = ProductVariation::count();
        
        $outOfStockSingle = $totalSingleProducts - $singleProductsCount;
        $outOfStockVariation = $totalVariations - $variationProductsCount;
        $outOfStockCount = $outOfStockSingle + $outOfStockVariation;

        // Stock value calculation
        $singleValue = $singleProducts->sum(function ($product) use ($warehouseId) {
            $stock = collect($product->warehouse_info ?? [])
                ->firstWhere('warehouse_id', (string) $warehouseId);
            $qty = $stock['quantity'] ?? 0;
            $price = $product->regular_price ?? 0;
            return $qty * $price;
        });

        // FIXED: Use correct column name for variation value
        $variationValue = DB::table('product_variation_stocks')
            ->join('product_variations', "product_variation_stocks.{$variationColumn}", '=', 'product_variations.id')
            ->where('product_variation_stocks.warehouse_id', $warehouseId)
            ->where('product_variation_stocks.quantity', '>', 0)
            ->sum(DB::raw('product_variation_stocks.quantity * product_variations.regular_price'));

        $totalValue = $singleValue + $variationValue;

        // Stock health percentage
        $stockHealth = $totalProducts > 0 
            ? round((($totalProducts - $outOfStockCount) / ($totalProducts + $outOfStockCount)) * 100, 1)
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
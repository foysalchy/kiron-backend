<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{HandleProductStockRequest, StoreProductRequest, UpdateProductRequest, UpdateStockRequest};
use App\Services\ProductService;
use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use App\Models\{BarCode as Barcode, Product, ProductVariation, ProductStockLedger, ProductVariationStockLedger, Purchase, PurchaseDetail, Order, OrderDetail};
use Illuminate\Http\{JsonResponse, Request};

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'brand_id' => $request->query('brand_id'),
            'status' => $request->query('status'),
            'type' => $request->query('type'),
            'product_type' => $request->query('product_type'),
            'stock_status' => $request->query('stock_status'),
            'purpose' => $request->query('purpose'),
            'source' => $request->query('source'),
            'warehouse_id' => $request->input('warehouse_id'),
            'mega_category_id' => array_filter((array) $request->input('mega_category_id')),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];
        $data = $this->productService->getAllProducts($filters, true);

        return ResponseHelper::success($data, 'Products retrieved successfully');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $this->productService->createProduct($request->validated());

        return ResponseHelper::success($data, 'Product created successfully', 201);
    }
    public function clone(Product $product): JsonResponse
    {
        $cloned = $this->productService->cloneProduct($product);
        return ResponseHelper::success($cloned, 'Product created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->productService->getProductById($id);

        return ResponseHelper::success($data, 'Product retrieved successfully');
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $data = $this->productService->updateProduct($id, $request->validated());

        return ResponseHelper::success($data, 'Product updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->deleteProduct($id);

        return ResponseHelper::success(null, 'Product deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->productService->restoreProduct($id);

        return ResponseHelper::success($data, 'Product restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->productService->forceDeleteProduct($id);

        return ResponseHelper::success(null, 'Product permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->productService->toggleStatus($id);

        return ResponseHelper::success($data, 'Product status updated successfully');
    }

    public function updateStock(UpdateStockRequest $request, int $id): JsonResponse
    {


        $data = $this->productService->updateStock($id, $request->validated());

        return ResponseHelper::success($data, 'Stock updated successfully');
    }

    /**
     * Add stock to warehouse
     */
    public function addStock(HandleProductStockRequest $request, int $id): JsonResponse
    {


        $data = $this->productService->addStockToWarehouse($id, $request->validated());

        return ResponseHelper::success($data, 'Stock added successfully');
    }

    /**
     * Remove stock from warehouse
     */
    public function removeStock(HandleProductStockRequest $request, int $id): JsonResponse
    {

        $data = $this->productService->removeStockFromWarehouse($id, $request->validated());

        return ResponseHelper::success($data, 'Stock removed successfully');
    }

    /**
     * Adjust stock (Manual Correction)
     */
    public function adjustStock(HandleProductStockRequest $request, int $id): JsonResponse
    {

        $data = $this->productService->adjustStock($id, $request->validated());

        return ResponseHelper::success($data, 'Stock adjusted successfully');
    }

    /**
     * Get stock history, batch movements, and ledger
     */
    public function stockHistory(Request $request, int $id): JsonResponse
    {
        $product = Product::with(['brand', 'variations.attributes.attributeGroup', 'variations.attributes.attributeValue'])->findOrFail($id);

        $warehouseId = $request->query('warehouse_id');
        $variationId = $request->query('variation_id');

        // 1. Fetch from ProductStockLedger / ProductVariationStockLedger
        $ledgers = collect();
        if ($product->type === 'variation' && $variationId) {
            $ledgerQuery = ProductVariationStockLedger::where('variation_id', $variationId)
                ->with(['warehouse', 'creator']);
            if ($warehouseId) {
                $ledgerQuery->where('warehouse_id', $warehouseId);
            }
            $ledgers = $ledgerQuery->orderBy('created_at', 'desc')->get();
        } else {
            $ledgerQuery = ProductStockLedger::where('product_id', $id)
                ->with(['warehouse', 'creator']);
            if ($warehouseId) {
                $ledgerQuery->where('warehouse_id', $warehouseId);
            }
            $ledgers = $ledgerQuery->orderBy('created_at', 'desc')->get();
        }

        $ledgerEntries = $ledgers->map(function ($item) {
            $refNo = null;
            if ($item->reference_type === 'Purchase' && $item->reference_id) {
                $p = Purchase::withoutGlobalScopes()->find($item->reference_id);
                $refNo = $p ? $p->reference_no : null;
            } elseif (in_array($item->reference_type, ['SalesOrder', 'POSOrder', 'LandingOrder', 'Order']) && $item->reference_id) {
                $o = Order::withoutGlobalScopes()->find($item->reference_id);
                $refNo = $o ? $o->order_no : null;
            }

            return [
                'id' => $item->id,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'transaction_type' => $item->transaction_type,
                'reference_type' => $item->reference_type,
                'reference_id' => $item->reference_id,
                'reference_no' => $refNo,
                'batch_number' => $item->batch_number,
                'quantity_before' => $item->quantity_before,
                'quantity_change' => $item->quantity_change,
                'quantity_after' => $item->quantity_after,
                'warehouse_name' => $item->warehouse ? $item->warehouse->name : null,
                'created_by' => $item->creator ? $item->creator->name : 'System',
                'notes' => $item->notes,
            ];
        });

        // 2. Fetch purchase history with batches & unit prices
        $purchases = PurchaseDetail::where('product_id', $id)
            ->with(['purchase.supplier', 'purchase.warehouse'])
            ->when($variationId, fn($q) => $q->where('variation_id', $variationId))
            ->get()
            ->map(function ($pd) {
                return [
                    'purchase_id' => $pd->purchase_id,
                    'reference_no' => $pd->purchase?->reference_no,
                    'purchase_date' => $pd->purchase?->purchase_date ? date('Y-m-d', strtotime($pd->purchase->purchase_date)) : null,
                    'supplier_name' => $pd->purchase?->supplier?->name ?? 'Unknown Supplier',
                    'warehouse_name' => $pd->purchase?->warehouse?->name ?? 'Default Warehouse',
                    'batch_number' => $pd->batch_number ?? $pd->purchase?->reference_no ?? 'Batch-1',
                    'quantity' => (int)$pd->quantity,
                    'purchase_price' => (float)$pd->purchase_price,
                    'unit_cost' => (float)$pd->unit_cost,
                    'total' => (float)$pd->total,
                ];
            });

        // 3. Fetch sales history
        $sales = OrderDetail::where('product_id', $id)
            ->with(['order.customer', 'order.warehouse'])
            ->when($variationId, fn($q) => $q->where('variation_id', $variationId))
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->map(function ($od) {
                return [
                    'order_id' => $od->order_id,
                    'order_no' => $od->order?->order_no,
                    'order_date' => $od->order?->order_date ? date('Y-m-d', strtotime($od->order->order_date)) : $od->created_at->format('Y-m-d'),
                    'customer_name' => $od->order?->customer?->name ?? 'Walk-in Customer',
                    'warehouse_name' => $od->order?->warehouse?->name ?? 'Default Warehouse',
                    'quantity' => (int)$od->quantity,
                    'unit_price' => (float)$od->unit_price,
                    'total' => (float)$od->total,
                    'status' => $od->order?->status,
                ];
            });

        // If ledger entries table was empty, construct virtual timeline from purchases and sales
        if ($ledgerEntries->isEmpty() && ($purchases->isNotEmpty() || $sales->isNotEmpty())) {
            $timeline = [];
            foreach ($purchases as $p) {
                $timeline[] = [
                    'id' => 'p_' . $p['purchase_id'],
                    'created_at' => $p['purchase_date'] ?? date('Y-m-d H:i:s'),
                    'transaction_type' => 'purchase',
                    'reference_type' => 'Purchase',
                    'reference_id' => $p['purchase_id'],
                    'reference_no' => $p['reference_no'],
                    'batch_number' => $p['batch_number'],
                    'quantity_before' => null,
                    'quantity_change' => (int)$p['quantity'],
                    'quantity_after' => null,
                    'warehouse_name' => $p['warehouse_name'],
                    'created_by' => 'Supplier: ' . $p['supplier_name'],
                    'notes' => 'Purchase @ ' . $p['purchase_price'] . ' BDT',
                ];
            }
            foreach ($sales as $s) {
                $timeline[] = [
                    'id' => 's_' . $s['order_id'],
                    'created_at' => $s['order_date'] ?? date('Y-m-d H:i:s'),
                    'transaction_type' => 'sale',
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $s['order_id'],
                    'reference_no' => $s['order_no'],
                    'batch_number' => null,
                    'quantity_before' => null,
                    'quantity_change' => -1 * (int)$s['quantity'],
                    'quantity_after' => null,
                    'warehouse_name' => $s['warehouse_name'],
                    'created_by' => 'Customer: ' . $s['customer_name'],
                    'notes' => 'Sale @ ' . $s['unit_price'] . ' BDT',
                ];
            }
            usort($timeline, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
            $ledgerEntries = collect($timeline);
        }

        return ResponseHelper::success([
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'sku' => $product->sku_code,
                'type' => $product->type,
                'manage_stock' => (bool)$product->manage_stock,
                'current_stock' => (int)$product->available_stock,
                'purchase_price' => (float)$product->purchase_price,
                'regular_price' => (float)$product->regular_price,
                'brand' => $product->brand?->name,
            ],
            'ledgers' => $ledgerEntries,
            'purchases' => $purchases,
            'sales' => $sales,
        ], 'Stock history retrieved successfully');
    }

    /**
     * Get current stock by warehouse
     */
    public function warehouseStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        $data = $this->productService->getCurrentStockByWarehouse($id, $request->warehouse_id);

        return ResponseHelper::success($data, 'Warehouse stock retrieved successfully');
    }

    public function generateBarcodes($productId)
    {
        $product = Product::with('variations')->findOrFail($productId);

        $barcodes = [];

        if ($product->variations->count() > 0) {

            foreach ($product->variations as $variation) {

                if ($variation->barcode) {
                    $barcodes[] = $variation->barcode;
                    continue;
                }

                $code = $variation->sku;

                if (!$code) {
                    do {
                        $code = mt_rand(1000000000, 9999999999);
                    } while (
                        Barcode::where('code', $code)->exists()
                    );
                }

                $barcode = $variation->barcode()->create([

                    'code' => $code
                ]);

                $barcodes[] = $barcode;
            }
        } else {
            // 👉 Case 2: simple product

            if (!$product->barcode) {
                $code = $product->sku_code;

                if (!$code) {
                    do {
                        $code = mt_rand(1000000000, 9999999999);
                    } while (Barcode::where('code', $code)->exists());
                }

                $barcode = $product->barcode()->create([
                    'code' => $code
                ]);

                $barcodes[] = $barcode;
            } else {
                $barcodes[] = $product->barcode;
            }
        }

        return response()->json($barcodes);
    }

    public function bulkGenerateBarcodes(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $products = Product::with('variations', 'barcode', 'variations.barcode')
            ->whereIn('id', $productIds)
            ->get();

        $generated = 0;
        $skipped = 0;

        foreach ($products as $product) {
            if ($product->variations->count() > 0) {
                foreach ($product->variations as $variation) {
                    if ($variation->barcode) {
                        $skipped++;
                        continue;
                    }
                    $code = $variation->sku;
                    if (!$code) {
                        do {
                            $code = mt_rand(1000000000, 9999999999);
                        } while (Barcode::where('code', $code)->exists());
                    }
                    $variation->barcode()->create(['code' => $code]);
                    $generated++;
                }
            } else {
                if ($product->barcode) {
                    $skipped++;
                    continue;
                }
                $code = $product->sku_code;
                if (!$code) {
                    do {
                        $code = mt_rand(1000000000, 9999999999);
                    } while (Barcode::where('code', $code)->exists());
                }
                $product->barcode()->create(['code' => $code]);
                $generated++;
            }
        }

        return response()->json([
            'generated' => $generated,
            'skipped'   => $skipped,
            'message'   => "{$generated} barcode(s) generated, {$skipped} skipped.",
        ]);
    }

    public function checkSku(Request $request): JsonResponse
    {
        $sku = $request->query('sku');
        $excludeId = $request->query('exclude_id');

        // products table-এ check — sku_code column
        $existsInProducts = Product::where('sku_code', $sku)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        // product_variations table-এ check — sku column
        $existsInVariations = ProductVariation::where('sku', $sku)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return response()->json([
            'available' => !$existsInProducts && !$existsInVariations,
        ]);
    }
}

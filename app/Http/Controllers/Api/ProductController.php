<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{HandleProductStockRequest, StoreProductRequest, UpdateProductRequest, UpdateStockRequest};
use App\Services\ProductService;
use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use App\Models\BarCode as Barcode;
use App\Models\Product;
use App\Models\ProductVariation;
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
            'stock_status' => $request->query('stock_status'),
            'purpose' => $request->query('purpose'),
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
     * Get stock history
     */
    public function stockHistory(Request $request, int $id): JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'transaction_type' => $request->query('transaction_type'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'limit' => $request->query('limit', 50),
        ];

        $data = $this->productService->getStockHistory($id, $filters);

        return ResponseHelper::success($data, 'Stock history retrieved successfully');
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreProductRequest, UpdateProductRequest, UpdateStockRequest};
use App\Services\ProductService;
use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'brand_id' => $request->query('brand_id'),
            'status' => $request->query('status'),
            'type' => $request->query('type'),
            'stock_status' => $request->query('stock_status'),
            'purpose' => $request->query('purpose'), // website or pos
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->productService->getAllProducts($filters, true);

        return ResponseHelper::success($data, 'Products retrieved successfully');
    }
    public function getByCompany(Request $request): JsonResponse
    {
        $filters = [
            'brand_id' => $request->query('brand_id'),
            'status' => $request->query('status'),
            'type' => $request->query('type'),
            'stock_status' => $request->query('stock_status'),
            'purpose' => $request->query('purpose'), // website or pos
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->productService->getAllCompanyProducts($filters,$request->user()->company_id, true);

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

  
}

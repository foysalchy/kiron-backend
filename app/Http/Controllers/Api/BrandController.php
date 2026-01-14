<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreBrandRequest, UpdateBrandRequest};
use App\Exceptions\ApiException;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->brandService->getAllBrands($filters, true);

        return ResponseHelper::success($data, 'Brands retrieved successfully');
    }
   

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $this->brandService->createBrand($request->validated());

        return ResponseHelper::success($data, 'Brand created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->brandService->getBrandById($id);

        return ResponseHelper::success($data, 'Brand retrieved successfully');
    }

    public function update(UpdateBrandRequest $request, int $id): JsonResponse
    {
        $data = $this->brandService->updateBrand($id, $request->validated());

        return ResponseHelper::success($data, 'Brand updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->brandService->deleteBrand($id);

        return ResponseHelper::success(null, 'Brand deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->brandService->restoreBrand($id);

        return ResponseHelper::success($data, 'Brand restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->brandService->forceDeleteBrand($id);

        return ResponseHelper::success(null, 'Brand permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->brandService->toggleStatus($id);

        return ResponseHelper::success($data, 'Brand status updated successfully');
    }

   
}

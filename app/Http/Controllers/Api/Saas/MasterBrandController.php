<?php

namespace App\Http\Controllers\Api\Saas;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\StoreMasterBrandRequest;
use App\Http\Requests\Saas\UpdateMasterBrandRequest;
use App\Services\Saas\MasterBrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterBrandController extends Controller
{
    public function __construct(protected MasterBrandService $brandService) {}

    /**
     * list all master brands with optional filters (status, search, pagination)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->brandService->getAllBrands($filters);
        return ResponseHelper::success($data, 'Master brands retrieved successfully');
    }

    /**
     * create a new master brand with logo
     */
    public function store(StoreMasterBrandRequest $request): JsonResponse
    {
        $data = $this->brandService->createBrand($request->validated());

        return ResponseHelper::success($data, 'Master brand created successfully');
    }

    /**
     * show a specific master brand
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->brandService->getBrandById($id);
        return ResponseHelper::success($data, 'Master brand details retrieved');
    }

    /**
     * update a specific master brand (with logo update)
     */
    public function update(UpdateMasterBrandRequest $request, int $id): JsonResponse
    {
        $data = $this->brandService->updateBrand($id, $request->validated());
        return ResponseHelper::success($data, 'Master brand updated successfully');
    }

    /**
     * delete a specific master brand
     */
    public function destroy(int $id): JsonResponse
    {
        $this->brandService->deleteBrand($id);
        return ResponseHelper::success(null, 'Master brand deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->brandService->restoreBrand($id);

        return ResponseHelper::success($data, 'Brand restore successfully');
    }
    public function forceDestroy(int $id): JsonResponse
    {
        $this->brandService->forceDeleteBrand($id);

        return ResponseHelper::success(null, 'Brand permanently deleted');
    }

    /**
     * toggle the status of a specific master brand
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->brandService->toggleStatus($id);
        return ResponseHelper::success($data, 'Brand status updated successfully');
    }
}

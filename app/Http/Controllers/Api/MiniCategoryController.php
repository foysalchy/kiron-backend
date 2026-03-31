<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Category\{StoreMiniCategoryRequest, UpdateMiniCategoryRequest};
use App\Services\MiniCategoryService;
use Illuminate\Http\JsonResponse;

class MiniCategoryController extends Controller
{
    public function __construct(
        protected MiniCategoryService $miniCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'sub_category_id' => $request->query('sub_category_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->miniCategoryService->getAllMiniCategories($filters, true);

        return  ResponseHelper::success($data, 'Mini categories retrieved successfully');
    }


    public function store(StoreMiniCategoryRequest $request): JsonResponse
    {
        $data = $this->miniCategoryService->createMiniCategory($request->validated());

        return  ResponseHelper::success($data, 'Mini category created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->miniCategoryService->getMiniCategoryById($id);

        return  ResponseHelper::success($data, 'Mini category retrieved successfully');
    }

    public function update(UpdateMiniCategoryRequest $request, int $id): JsonResponse
    {
        $data = $this->miniCategoryService->updateMiniCategory($id, $request->validated());

        return  ResponseHelper::success($data, 'Mini category updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->miniCategoryService->deleteMiniCategory($id);

        return  ResponseHelper::success(null, 'Mini category deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->miniCategoryService->restoreMiniCategory($id);

        return  ResponseHelper::success($data, 'Mini category restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->miniCategoryService->forceDeleteMiniCategory($id);

        return  ResponseHelper::success(null, 'Mini category permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->miniCategoryService->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }

    public function getBySubCategory(Request $request): JsonResponse
    {
        $subId = $request->query('sub_category_id');

        if (!$subId) {
            throw ApiException::badRequest('Sub Category ID is required');
        }

        $data = $this->miniCategoryService->getBySubCategory($subId);

        return  ResponseHelper::success($data, 'Mini categories retrieved successfully');
    }
}

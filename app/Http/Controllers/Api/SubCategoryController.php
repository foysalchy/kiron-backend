<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Category\{StoreSubCategoryRequest, UpdateSubCategoryRequest};
use App\Services\SubCategoryService;
use Illuminate\Http\JsonResponse;

class SubCategoryController extends Controller
{
    public function __construct(
        protected SubCategoryService $subCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'mega_category_id' => $request->query('mega_category_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $columns = ['*'];
        if ($request->has('select')) {
            $select = $request->query('select');
            $columns = is_string($select) ? explode(',', $select) : $select;
        }

        if ($request->has('with')) {
            $with = is_string($request->query('with')) ? explode(',', $request->query('with')) : $request->query('with');
            if (empty($with) || $with[0] === '') $with = [];
            $filters['with'] = $with;
        }

        $data = $this->subCategoryService->getAllSubCategories($filters, true, $columns);

        return ResponseHelper::success($data, 'Sub categories retrieved successfully');
    }

    public function store(StoreSubCategoryRequest $request): JsonResponse
    {
        $data = $this->subCategoryService->createSubCategory($request->validated());

        return  ResponseHelper::success($data, 'Sub category created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->subCategoryService->getSubCategoryById($id);

        return  ResponseHelper::success($data, 'Sub category retrieved successfully');
    }

    public function update(UpdateSubCategoryRequest $request, int $id): JsonResponse
    {
        $data = $this->subCategoryService->updateSubCategory($id, $request->validated());

        return  ResponseHelper::success($data, 'Sub category updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->subCategoryService->deleteSubCategory($id);

        return  ResponseHelper::success(null, 'Sub category deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->subCategoryService->restoreSubCategory($id);

        return  ResponseHelper::success($data, 'Sub category restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->subCategoryService->forceDeleteSubCategory($id);

        return  ResponseHelper::success(null, 'Sub category permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->subCategoryService->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }

    public function getByMegaCategory(Request $request): JsonResponse
    {
        $megaId = $request->query('mega_category_id');


        if (!$megaId) {
            throw ApiException::badRequest('Mega Category ID and is required');
        }

        $data = $this->subCategoryService->getByMegaCategory($megaId);

        return ResponseHelper::success($data, 'Sub categories retrieved successfully');
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->query('term');

        if (!$term) {
            throw ApiException::badRequest('Search term is required');
        }

        $data = $this->subCategoryService->searchSubCategories(
            $term
        );

        return  ResponseHelper::success($data, 'Search results retrieved successfully');
    }
}

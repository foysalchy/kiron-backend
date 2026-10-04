<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Category\{StoreExtraCategoryRequest, UpdateExtraCategoryRequest};
use App\Services\ExtraCategoryService;
use Illuminate\Http\JsonResponse;

class ExtraCategoryController extends Controller
{
    public function __construct(
        protected ExtraCategoryService $extraCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'mini_category_id' => $request->query('mini_category_id'),
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

        $data = $this->extraCategoryService->getAllExtraCategories($filters, true, $columns);

        return  ResponseHelper::success($data, 'Extra categories retrieved successfully');
    }

    public function store(StoreExtraCategoryRequest $request): JsonResponse
    {
        $data = $this->extraCategoryService->createExtraCategory($request->validated());

        return  ResponseHelper::success($data, 'Extra category created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->extraCategoryService->getExtraCategoryById($id);

        return  ResponseHelper::success($data, 'Extra category retrieved successfully');
    }

    public function update(UpdateExtraCategoryRequest $request, int $id): JsonResponse
    {
        $data = $this->extraCategoryService->updateExtraCategory($id, $request->validated());

        return  ResponseHelper::success($data, 'Extra category updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->extraCategoryService->deleteExtraCategory($id);

        return ResponseHelper::success(null, 'Extra category deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->extraCategoryService->restoreExtraCategory($id);

        return  ResponseHelper::success($data, 'Extra category restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->extraCategoryService->forceDeleteExtraCategory($id);

        return  ResponseHelper::success(null, 'Extra category permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->extraCategoryService->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }

    public function getByMiniCategory(Request $request): JsonResponse
    {
        $miniId = $request->query('mini_category_id');

        if (!$miniId) {
            throw ApiException::badRequest('Mini Category ID  are required');
        }

        $data = $this->extraCategoryService->getByMiniCategory($miniId);

        return ResponseHelper::success($data, 'Extra categories retrieved successfully');
    }

}

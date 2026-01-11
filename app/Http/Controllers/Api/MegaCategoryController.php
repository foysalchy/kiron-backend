<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\{StoreMegaCategoryRequest, UpdateMegaCategoryRequest};
use App\Services\MegaCategoryService;
use App\Exceptions\ApiException;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};


class MegaCategoryController extends Controller
{
    public function __construct(
        protected MegaCategoryService $megaCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->megaCategoryService->getAllMegaCategories($filters, true);

        return ResponseHelper::success($data, 'Mega categories retrieved successfully');
    }

    public function store(StoreMegaCategoryRequest $request): JsonResponse
    {
        $data = $this->megaCategoryService->createMegaCategory($request->validated());

        return  ResponseHelper::success($data, 'Mega category created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->megaCategoryService->getMegaCategoryById($id);

        return  ResponseHelper::success($data, 'Mega category retrieved successfully');
    }

    public function update(UpdateMegaCategoryRequest $request, int $id): JsonResponse
    {
        $data = $this->megaCategoryService->updateMegaCategory($id, $request->validated());

        return ResponseHelper::success($data, 'Mega category updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->megaCategoryService->deleteMegaCategory($id);

        return ResponseHelper::success(null, 'Mega category deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->megaCategoryService->restoreMegaCategory($id);

        return  ResponseHelper::success($data, 'Mega category restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->megaCategoryService->forceDeleteMegaCategory($id);

        return ResponseHelper::success(null, 'Mega category permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->megaCategoryService->toggleStatus($id);

        return  ResponseHelper::success($data, 'Status updated successfully');
    }

  
}

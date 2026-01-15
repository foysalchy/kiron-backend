<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService
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

        $data = $this->pageService->getAllPages($filters, true);

        return ResponseHelper::success($data, 'Pages retrieved successfully');
    }
    public function store(StorePageRequest $request): JsonResponse
    {
        $data = $this->pageService->createPage($request->validated());

        return ResponseHelper::success($data, 'Page created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->pageService->getPageById($id);

        return ResponseHelper::success($data, 'Page retrieved successfully');
    }

    public function update(UpdatePageRequest $request, int $id): JsonResponse
    {
        $data = $this->pageService->updatePage($id, $request->validated());

        return ResponseHelper::success($data, 'Page updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->pageService->deletePage($id);

        return ResponseHelper::success(null, 'Page deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->pageService->restorePage($id);

        return ResponseHelper::success($data, 'Page restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->pageService->forceDeletePage($id);

        return ResponseHelper::success(null, 'Page permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->pageService->toggleStatus($id);

        return ResponseHelper::success($data, 'Page status updated successfully');
    }
}

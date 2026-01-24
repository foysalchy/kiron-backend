<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreLandingPageRequest, UpdateLandingPageRequest};
use App\Services\LandingPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __construct(
        protected LandingPageService $landingPageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'template_id' => $request->query('template_id'),
            'product_id' => $request->query('product_id'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->landingPageService->getAllLandingPages($filters, true);

        return ResponseHelper::success($data, 'Landing pages retrieved successfully');
    }

    public function store(StoreLandingPageRequest $request): JsonResponse
    {
        $data = $this->landingPageService->createLandingPage($request->validated());

        return ResponseHelper::success($data, 'Landing page created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->landingPageService->getLandingPageById($id);

        return ResponseHelper::success($data, 'Landing page retrieved successfully');
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $data = $this->landingPageService->getLandingPageBySlug($slug);

        return ResponseHelper::success($data, 'Landing page retrieved successfully');
    }

    public function update(UpdateLandingPageRequest $request, int $id): JsonResponse
    {
        $data = $this->landingPageService->updateLandingPage($id, $request->validated());

        return ResponseHelper::success($data, 'Landing page updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->landingPageService->deleteLandingPage($id);

        return ResponseHelper::success(null, 'Landing page deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->landingPageService->restoreLandingPage($id);

        return ResponseHelper::success($data, 'Landing page restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->landingPageService->forceDeleteLandingPage($id);

        return ResponseHelper::success(null, 'Landing page permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->landingPageService->toggleStatus($id);

        return ResponseHelper::success($data, 'Landing page status updated successfully');
    }
}
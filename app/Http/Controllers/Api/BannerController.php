<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Services\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $bannerService
    ) {}
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'title' => $request->query('title'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->bannerService->getAllBanners($filters, true);

        return ResponseHelper::success($data, 'Banners retrieved successfully');
    }
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $this->bannerService->createBanner($request->validated());

        return ResponseHelper::success($data, 'Banner created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->bannerService->getBannerById($id);

        return ResponseHelper::success($data, 'Banner retrieved successfully');
    }

    public function update(UpdateBannerRequest $request, int $id): JsonResponse
    {
        $data = $this->bannerService->updateBanner($id, $request->validated());

        return ResponseHelper::success($data, 'Banner updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bannerService->deleteBanner($id);

        return ResponseHelper::success(null, 'Banner deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->bannerService->restoreBanner($id);

        return ResponseHelper::success($data, 'Banner restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->bannerService->forceDeleteBanner($id);

        return ResponseHelper::success(null, 'Banner permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->bannerService->toggleStatus($id);

        return ResponseHelper::success($data, 'Banner status updated successfully');
    }
}

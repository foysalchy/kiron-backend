<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSliderRequest;
use App\Http\Requests\UpdateSliderRequest;
use App\Services\SliderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function __construct(
        protected SliderService $sliderService
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

        $data = $this->sliderService->getAllSliders($filters, true);

        return ResponseHelper::success($data, 'Sliders retrieved successfully');
    }
    public function store(StoreSliderRequest $request): JsonResponse
    {
        $data = $this->sliderService->createSlider($request->validated());

        return ResponseHelper::success($data, 'Slider created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->sliderService->getSliderById($id);

        return ResponseHelper::success($data, 'Slider retrieved successfully');
    }

    public function update(UpdateSliderRequest $request, int $id): JsonResponse
    {
        $data = $this->sliderService->updateSlider($id, $request->validated());

        return ResponseHelper::success($data, 'Slider updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->sliderService->deleteSlider($id);

        return ResponseHelper::success(null, 'Slider deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->sliderService->restoreSlider($id);

        return ResponseHelper::success($data, 'Slider restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->sliderService->forceDeleteSlider($id);

        return ResponseHelper::success(null, 'Slider permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->sliderService->toggleStatus($id);

        return ResponseHelper::success($data, 'Slider status updated successfully');
    }
}

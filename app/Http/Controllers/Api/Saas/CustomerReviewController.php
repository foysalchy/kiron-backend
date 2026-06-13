<?php

namespace App\Http\Controllers\Api\Saas;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\StoreCustomerReviewRequest;
use App\Http\Requests\Saas\UpdateCustomerReviewRequest;
use App\Services\Saas\CustomerReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function __construct(protected CustomerReviewService $reviewService)
    {}

    /**
     * list all customer reviews with optional filters
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

        $data = $this->reviewService->getAllReviews($filters);
        return ResponseHelper::success($data, 'Customer reviews retrieved successfully');
    }

    /**
     * create a new customer review
     */
    public function store(StoreCustomerReviewRequest $request): JsonResponse
    {
        $data = $this->reviewService->createReview($request->validated());
        return ResponseHelper::success($data, 'Customer review created successfully');
    }

    /**
     * show a specific review
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->reviewService->getReviewById($id);
        return ResponseHelper::success($data, 'Customer review details retrieved');
    }

    /**
     * update a specific review
     */
    public function update(UpdateCustomerReviewRequest $request, int $id): JsonResponse
    {
        $data = $this->reviewService->updateReview($id, $request->validated());
        return ResponseHelper::success($data, 'Customer review updated successfully');
    }

    /**
     * soft delete
     */
    public function destroy(int $id): JsonResponse
    {
        $this->reviewService->deleteReview($id);
        return ResponseHelper::success(null, 'Customer review deleted successfully');
    }

    /**
     * restore review
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->reviewService->restoreReview($id);
        return ResponseHelper::success($data, 'Customer review restored successfully');
    }

    /**
     * permanent delete
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->reviewService->forceDeleteReview($id);
        return ResponseHelper::success(null, 'Customer review permanently deleted');
    }

    /**
     * toggle status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->reviewService->toggleStatus($id);
        return ResponseHelper::success($data, 'Review status updated successfully');
    }
}

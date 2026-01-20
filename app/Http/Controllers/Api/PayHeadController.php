<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayHeadRequest;
use App\Http\Requests\UpdatePayHeadRequest;
use App\Services\PayHeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayHeadController extends Controller
{
    public function __construct(
        protected PayHeadService $payHeadService
    ){}
    /**
     * Display a listing of pay heads.
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

        $payHeads = $this->payHeadService->getAllPayHeads($filters, true);

        return ResponseHelper::success($payHeads, 'Pay heads retrieved successfully');
    }

    /**
     * Store a newly created pay head.
     */
    public function store(StorePayHeadRequest $request): JsonResponse
    {
        $payHead = $this->payHeadService->createPayHead($request->validated());

        return ResponseHelper::created($payHead, 'Pay head created successfully');
    }

    /**
     * Display the specified pay head.
     */
    public function show(int $id): JsonResponse
    {
        $payHead = $this->payHeadService->getPayHeadById($id);

        return ResponseHelper::success($payHead, 'Pay head retrieved successfully');
    }

    /**
     * Update the specified pay head.
     */
    public function update(UpdatePayHeadRequest $request, int $id): JsonResponse
    {
        $payHead = $this->payHeadService->updatePayHead($id, $request->validated());

        return ResponseHelper::success($payHead, 'Pay head updated successfully');
    }

    /**
     * Remove the specified pay head (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->payHeadService->deletePayHead($id);

        return ResponseHelper::success(null, 'Pay head deleted successfully');
    }

    /**
     * Restore soft deleted pay head.
     */
    public function restore(int $id): JsonResponse
    {
        $party = $this->payHeadService->restorePayHead($id);

        return ResponseHelper::success($party, 'Pay head restored successfully');
    }

    /**
     * Permanently delete pay head.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->payHeadService->forceDeletePayHead($id);

        return ResponseHelper::success(null, 'Pay head permanently deleted');
    }

    /**
     * Toggle pay head status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $payHead = $this->payHeadService->toggleStatus($id);

        return ResponseHelper::success($payHead, 'Pay head status updated successfully');
    }
}

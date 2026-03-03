<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreAssetDisposalRequest;
use App\Http\Requests\Asset\UpdateAssetDisposalRequest;
use App\Services\AssetDisposalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetDisposalController extends Controller
{
    public function __construct(protected AssetDisposalService $disposalService)
    {}

    /**
     * Display a listing of asset disposals with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->disposalService->getAllDisposals($request->all());
        return ResponseHelper::success($data, 'Asset disposals retrieved successfully');
    }

    /**
     * Store a newly created asset disposal.
     */
    public function store(StoreAssetDisposalRequest $request): JsonResponse
    {
        $data = $this->disposalService->createDisposal($request->validated());
        return ResponseHelper::success($data, 'Asset disposal created successfully', 201);
    }

    /**
     * Display the specified asset disposal.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->disposalService->getDisposalById($id);
        return ResponseHelper::success($data, 'Asset disposal details retrieved successfully');
    }

    /**
     * Update the specified asset disposal.
     */
    public function update(UpdateAssetDisposalRequest $request, int $id): JsonResponse
    {
        $data = $this->disposalService->updateDisposal($id, $request->validated());
        return ResponseHelper::success($data, 'Asset disposal updated successfully');
    }

    /**
     * Remove the specified asset disposal (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->disposalService->deleteDisposal($id);
        return ResponseHelper::success(null, 'Asset disposal deleted successfully');
    }

    /**
     * Restore a soft-deleted asset disposal.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->disposalService->restoreDisposal($id);
        return ResponseHelper::success($data, 'Asset disposal restored successfully');
    }

    /**
     * Permanently delete an asset disposal.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->disposalService->forceDeleteDisposal($id);
        return ResponseHelper::success(null, 'Asset disposal permanently deleted');
    }

    /**
     * Update the status of an asset disposal.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required',Rule::in([Status::Active->value,Status::Inactive->value,Status::Disposed->value])]
        ]);

        $data = $this->disposalService->updateStatus($id, $request->status);
        return ResponseHelper::success($data, 'Asset disposal status updated successfully');
    }
}

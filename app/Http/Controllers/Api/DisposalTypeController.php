<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\StoreDisposalTypeRequest;
use App\Http\Requests\Asset\UpdateDisposalTypeRequest;
use App\Services\DisposalTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisposalTypeController extends Controller
{
    public function __construct(protected DisposalTypeService $typeService) {}

    /**
     * list disposal type.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->typeService->getAllTypes($request->all());
        return ResponseHelper::success($data, 'Disposal types retrieved successfully');
    }
    /**
     * create a disposal type.
     */

    public function store(StoreDisposalTypeRequest $request): JsonResponse
    {
        $data = $this->typeService->createType($request->validated());
        return ResponseHelper::success($data, 'Disposal type created successfully', 201);
    }

    /**
     * show a disposal type.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->typeService->getTypeById($id);
        return ResponseHelper::success($data, 'Disposal type details retrieved');
    }
    /**
     * update a disposal type.
     */

    public function update(int $id, UpdateDisposalTypeRequest $request): JsonResponse
    {
        $data = $this->typeService->updateType($id, $request->validated());
        return ResponseHelper::success($data, 'Disposal type updated successfully');
    }
    /**
     * soft delete a disposal type.
     */

    public function destroy(int $id): JsonResponse
    {
        $this->typeService->deleteType($id);
        return ResponseHelper::success(null, 'Disposal type deleted successfully');
    }
    /**
     * Restore a soft-deleted disposal type.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->typeService->restoreType($id);
        return ResponseHelper::success($data, 'Disposal type restored successfully');
    }

    /**
     * Permanently delete a disposal type.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->typeService->forceDeleteType($id);
        return ResponseHelper::success(null, 'Disposal type permanently deleted');
    }
    /**
     * status a disposal type.
     */

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->typeService->toggleStatus($id);
        return ResponseHelper::success($data, 'Status toggled successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpDirectoryRequest;
use App\Http\Requests\UpdateIpDirectoryRequest;
use App\Services\IpDirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpDirectoryController extends Controller
{ 
    public function __construct(
        protected IpDirectoryService $ipDirectoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->ipDirectoryService->getAllIps($filters, true);

        return ResponseHelper::success($data, 'IP directory retrieved successfully');
    }

    public function store(StoreIpDirectoryRequest $request): JsonResponse
    {
        $data = $this->ipDirectoryService->createIp($request->validated());

        return ResponseHelper::success($data, 'IP added to directory successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->ipDirectoryService->getIpById($id);

        return ResponseHelper::success($data, 'IP details retrieved successfully');
    }

    public function update(UpdateIpDirectoryRequest $request, int $id): JsonResponse
    {
        $data = $this->ipDirectoryService->updateIp($id, $request->validated());

        return ResponseHelper::success($data, 'IP directory updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->ipDirectoryService->deleteIp($id);

        return ResponseHelper::success(null, 'IP deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->ipDirectoryService->restoreIp($id);

        return ResponseHelper::success($data, 'IP restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->ipDirectoryService->forceDeleteIp($id);

        return ResponseHelper::success(null, 'IP permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->ipDirectoryService->toggleStatus($id);

        return ResponseHelper::success($data, 'IP status updated successfully');
    }
}

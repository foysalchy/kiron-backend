<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpSettingRequest;
use App\Http\Requests\UpdateIpSettingRequest;
use App\Services\IpSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpSettingController extends Controller
{ 
    public function __construct(
        protected IpSettingService $ipSettingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->ipSettingService->getAllIpSettings($filters, true);

        return ResponseHelper::success($data, 'IP settings retrieved successfully');
    }

    public function store(StoreIpSettingRequest $request): JsonResponse
    {
        $data = $this->ipSettingService->createIpSetting($request->validated());

        return ResponseHelper::success($data, 'IP setting created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->ipSettingService->getIpSettingById($id);

        return ResponseHelper::success($data, 'IP setting retrieved successfully');
    }

    public function update(UpdateIpSettingRequest $request, int $id): JsonResponse
    {
        $data = $this->ipSettingService->updateIpSetting($id, $request->validated());

        return ResponseHelper::success($data, 'IP setting updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->ipSettingService->deleteIpSetting($id);

        return ResponseHelper::success(null, 'IP setting deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->ipSettingService->restoreIpSetting($id);

        return ResponseHelper::success($data, 'IP setting restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->ipSettingService->forceDeleteIpSetting($id);

        return ResponseHelper::success(null, 'IP setting permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->ipSettingService->toggleStatus($id);

        return ResponseHelper::success($data, 'IP setting status updated successfully');
    }
}

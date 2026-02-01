<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsSettingRequest;
use App\Http\Requests\UpdateSmsSettingRequest;
use App\Services\SmsSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsSettingController extends Controller
{ 
    public function __construct(protected SmsSettingService $smsSettingService)
    {
    }
    /**
     * Display a listing of the SMS settings.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'event_name' => $request->query('event_name'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->smsSettingService->getAllSmsSettings($filters, true);

        return ResponseHelper::success($data, 'SMS settings retrieved successfully');
    }

    /**
     * Store a newly created SMS setting.
     */
    public function store(StoreSmsSettingRequest $request): JsonResponse
    {
        $data = $this->smsSettingService->createSmsSetting($request->validated());

        return ResponseHelper::success($data, 'SMS setting created successfully', 201);
    }

    /**
     * Display the specified SMS setting.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->smsSettingService->getSmsSettingById($id);

        return ResponseHelper::success($data, 'SMS setting retrieved successfully');
    }

    /**
     * Update the specified SMS setting.
     */
    public function update(UpdateSmsSettingRequest $request, int $id): JsonResponse
    {
        $data = $this->smsSettingService->updateSmsSetting($id, $request->validated());

        return ResponseHelper::success($data, 'SMS setting updated successfully');
    }

    /**
     * Remove the specified SMS setting (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->smsSettingService->deleteSmsSetting($id);

        return ResponseHelper::success(null, 'SMS setting deleted successfully');
    }

    /**
     * Restore the soft deleted SMS setting.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->smsSettingService->restoreSmsSetting($id);

        return ResponseHelper::success($data, 'SMS setting restored successfully');
    }

    /**
     * Permanently delete the specified SMS setting.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->smsSettingService->forceDeleteSmsSetting($id);

        return ResponseHelper::success(null, 'SMS setting permanently deleted');
    }

    /**
     * Toggle the status of the SMS setting.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->smsSettingService->toggleStatus($id);

        return ResponseHelper::success($data, 'SMS setting status updated successfully');
    }
}

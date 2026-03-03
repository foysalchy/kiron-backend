<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sms\StoreSmsTemplateRequest;
use App\Http\Requests\Sms\UpdateSmsTemplateRequest;
use App\Services\SmsTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function __construct(protected SmsTemplateService $templateService)
    {}

    /**
     * list SMS template.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->templateService->getAllTemplates($request->all());
        return ResponseHelper::success($data, 'SMS templates retrieved successfully');
    }

    /**
     * store of a SMS template.
     */
    public function store(StoreSmsTemplateRequest $request): JsonResponse
    {
        $data = $this->templateService->createTemplate($request->validated());
        return ResponseHelper::success($data, 'SMS template created successfully', 201);
    }

    /**
     * show of a SMS template.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->templateService->getTemplateById($id);
        return ResponseHelper::success($data, 'SMS template details retrieved');
    }
    /**
     * update of a SMS template.
     */

    public function update(UpdateSmsTemplateRequest $request, int $id): JsonResponse
    {
        $data = $this->templateService->updateTemplate($id, $request->validated());
        return ResponseHelper::success($data, 'SMS template updated successfully');
    }
    /**
     * destroy of a SMS template.
     */

    public function destroy(int $id): JsonResponse
    {
        $this->templateService->deleteTemplate($id);
        return ResponseHelper::success(null, 'SMS template deleted successfully');
    }

    /**
     * restore of a SMS template.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->templateService->restoreTemplate($id);
        return ResponseHelper::success($data, 'SMS template restored successfully');
    }

    /**
     * permanently delete of a SMS template.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->templateService->forceDeleteTemplate($id);
        return ResponseHelper::success(null, 'SMS template permanently deleted');
    }
    /**
     * Toggle the status of a SMS template.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->templateService->toggleStatus($id);
        return ResponseHelper::success($data, 'SMS template status toggled successfully');
    }
}

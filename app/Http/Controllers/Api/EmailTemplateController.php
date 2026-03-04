<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Email\StoreEmailTemplateRequest;
use App\Http\Requests\Email\UpdateEmailTemplateRequest;
use App\Services\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * EmailTemplateController constructor.
     */
    public function __construct(protected EmailTemplateService $service)
    {}

    /**
     * Display a listing of email templates.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getAllTemplates($request->all());
        return ResponseHelper::success($data, 'Email templates retrieved successfully');
    }

    /**
     * Store a newly created email template in storage.
     */
    public function store(StoreEmailTemplateRequest $request): JsonResponse
    {
        $data = $this->service->createTemplate($request->validated());
        return ResponseHelper::success($data, 'Email template created successfully', 201);
    }

    /**
     * Display the specified email template details.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->service->getTemplateById($id);
        return ResponseHelper::success($data, 'Email template details retrieved');
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(UpdateEmailTemplateRequest $request, int $id): JsonResponse
    {
        $data = $this->service->updateTemplate($id, $request->validated());
        return ResponseHelper::success($data, 'Email template updated successfully');
    }

    /**
     * Remove the specified email template from storage (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteTemplate($id);
        return ResponseHelper::success(null, 'Email template moved to trash');
    }
    /**
     * Restore a soft-deleted email template.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->service->restoreTemplate($id);
        return ResponseHelper::success($data, 'Email template restored successfully');
    }

    /**
     * Permanently delete an email template from the database.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->service->forceDeleteTemplate($id);
        return ResponseHelper::success(null, 'Email template permanently deleted');
    }

    /**
     * Toggle the status of the email template (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->service->toggleStatus($id);
        return ResponseHelper::success($data, 'Status updated successfully');
    }


}

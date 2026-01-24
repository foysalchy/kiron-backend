<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreTemplateRequest, UpdateTemplateRequest};
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->templateService->getAllTemplates($filters, true);

        return ResponseHelper::success($data, 'Templates retrieved successfully');
    }

    public function store(StoreTemplateRequest $request): JsonResponse
    {
        $data = $this->templateService->createTemplate($request->validated());

        return ResponseHelper::success($data, 'Template created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->templateService->getTemplateById($id);

        return ResponseHelper::success($data, 'Template retrieved successfully');
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $data = $this->templateService->getTemplateBySlug($slug);

        return ResponseHelper::success($data, 'Template retrieved successfully');
    }

    public function update(UpdateTemplateRequest $request, int $id): JsonResponse
    {
        $data = $this->templateService->updateTemplate($id, $request->validated());

        return ResponseHelper::success($data, 'Template updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->templateService->deleteTemplate($id);

        return ResponseHelper::success(null, 'Template deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->templateService->restoreTemplate($id);

        return ResponseHelper::success($data, 'Template restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->templateService->forceDeleteTemplate($id);

        return ResponseHelper::success(null, 'Template permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->templateService->toggleStatus($id);

        return ResponseHelper::success($data, 'Template status updated successfully');
    }
}
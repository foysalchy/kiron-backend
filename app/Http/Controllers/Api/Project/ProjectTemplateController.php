<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTemplateController extends Controller
{
    public function __construct(
        protected ProjectTemplateService $templateService
    ) {}

    public function index(): JsonResponse
    {
        $templates = $this->templateService->getTemplates();
        return ResponseHelper::success($templates, 'Templates retrieved');
    }

    public function show(int $id): JsonResponse
    {
        $template = $this->templateService->getTemplateById($id);
        return ResponseHelper::success($template, 'Template retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_type' => 'required|string',
            'description' => 'nullable|string',
            'structure_json' => 'required|array',
            'is_default' => 'nullable|boolean',
        ]);

        $template = $this->templateService->createTemplate($validated);
        return ResponseHelper::created($template, 'Template created');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $template = $this->templateService->updateTemplate($id, $request->all());
        return ResponseHelper::success($template, 'Template updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->templateService->deleteTemplate($id);
        return ResponseHelper::success(null, 'Template deleted');
    }
}

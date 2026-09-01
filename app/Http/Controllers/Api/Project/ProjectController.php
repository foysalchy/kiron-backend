<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'project_type', 'search']);
        $perPage = (int)$request->get('per_page', 15);
        $projects = $this->projectService->getProjects($filters, $perPage);

        return ResponseHelper::success($projects, 'Projects retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $project = $this->projectService->getProjectById($id);
        return ResponseHelper::success($project, 'Project details retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'project_type' => 'required|string',
            'customer_id' => 'nullable|exists:parties,id',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'required|string',
            'project_manager_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'billing_type' => 'nullable|string',
            'contract_value' => 'nullable|numeric',
            'budget' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'tags' => 'nullable|array',
            'members' => 'nullable|array',
            'template_id' => 'nullable|exists:project_templates,id',
        ]);

        $project = $this->projectService->createProject($validated);
        return ResponseHelper::created($project, 'Project created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'project_type' => 'sometimes|required|string',
            'customer_id' => 'nullable|exists:parties,id',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'status' => 'nullable|string',
            'project_manager_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'billing_type' => 'nullable|string',
            'contract_value' => 'nullable|numeric',
            'budget' => 'nullable|numeric',
            'progress' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'tags' => 'nullable|array',
            'members' => 'nullable|array',
        ]);

        $project = $this->projectService->updateProject($id, $validated);
        return ResponseHelper::success($project, 'Project updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->projectService->deleteProject($id);
        return ResponseHelper::success(null, 'Project deleted successfully');
    }

    public function applyTemplate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|exists:project_templates,id',
        ]);

        $this->projectService->applyTemplate($id, (int)$request->template_id);
        $project = $this->projectService->getProjectById($id);

        return ResponseHelper::success($project, 'Template applied successfully');
    }
}

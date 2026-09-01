<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = ProjectMilestone::with(['phase', 'tasks']);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return ResponseHelper::success($query->get(), 'Milestones retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'cost_amount' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);

        $milestone = ProjectMilestone::create($validated);
        return ResponseHelper::created($milestone, 'Milestone created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $milestone = ProjectMilestone::findOrFail($id);
        $milestone->update($request->all());
        return ResponseHelper::success($milestone, 'Milestone updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $milestone = ProjectMilestone::findOrFail($id);
        $milestone->delete();
        return ResponseHelper::success(null, 'Milestone deleted successfully');
    }
}

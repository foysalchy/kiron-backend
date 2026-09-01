<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = ProjectMember::with(['employee.department', 'user']);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return ResponseHelper::success($query->get(), 'Team members retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
            'role' => 'nullable|string',
            'cost_type' => 'nullable|string',
            'cost_rate' => 'nullable|numeric',
            'billable_rate' => 'nullable|numeric',
            'assigned_date' => 'nullable|date',
            'release_date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $member = ProjectMember::create($validated);
        return ResponseHelper::created($member->load(['employee', 'user']), 'Member assigned successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $member = ProjectMember::findOrFail($id);
        $member->update($request->all());
        return ResponseHelper::success($member->load(['employee', 'user']), 'Member updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $member = ProjectMember::findOrFail($id);
        $member->delete();
        return ResponseHelper::success(null, 'Member removed successfully');
    }
}

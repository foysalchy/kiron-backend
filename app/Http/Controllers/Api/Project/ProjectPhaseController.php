<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectPhase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPhaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = ProjectPhase::withCount('tasks');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return ResponseHelper::success($query->orderBy('sort_order')->get(), 'Phases retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric',
            'status' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $phase = ProjectPhase::create($validated);
        return ResponseHelper::created($phase, 'Phase created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $phase = ProjectPhase::findOrFail($id);
        $phase->update($request->all());
        return ResponseHelper::success($phase, 'Phase updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $phase = ProjectPhase::findOrFail($id);
        $phase->delete();
        return ResponseHelper::success(null, 'Phase deleted successfully');
    }
}

<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectTaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectTaskStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = ProjectTaskStatus::orderBy('sort_order')->get();

        // If no custom statuses exist for this company, create standard defaults
        if ($statuses->isEmpty()) {
            $defaults = [
                ['name' => 'To Do', 'slug' => 'todo', 'color' => '#6b7280', 'bg_color' => '#f3f4f6', 'sort_order' => 1, 'is_default' => true, 'is_completed' => false],
                ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => '#2563eb', 'bg_color' => '#eff6ff', 'sort_order' => 2, 'is_default' => true, 'is_completed' => false],
                ['name' => 'In Review', 'slug' => 'review', 'color' => '#d97706', 'bg_color' => '#fffbeb', 'sort_order' => 3, 'is_default' => true, 'is_completed' => false],
                ['name' => 'Completed', 'slug' => 'completed', 'color' => '#059669', 'bg_color' => '#ecfdf5', 'sort_order' => 4, 'is_default' => true, 'is_completed' => true],
            ];
            foreach ($defaults as $item) {
                ProjectTaskStatus::create($item);
            }
            $statuses = ProjectTaskStatus::orderBy('sort_order')->get();
        }

        return ResponseHelper::success($statuses, 'Task statuses retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:30',
            'bg_color' => 'nullable|string|max:30',
            'is_completed' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name'], '_');
        $validated['color'] = $validated['color'] ?? '#13565e';
        $validated['bg_color'] = $validated['bg_color'] ?? '#e6f4f5';

        $status = ProjectTaskStatus::create($validated);
        return ResponseHelper::created($status, 'Task status created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $status = ProjectTaskStatus::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'color' => 'nullable|string|max:30',
            'bg_color' => 'nullable|string|max:30',
            'is_completed' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name'], '_');
        }

        $status->update($validated);
        return ResponseHelper::success($status, 'Task status updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $status = ProjectTaskStatus::findOrFail($id);
        if ($status->is_default) {
            return ResponseHelper::error('Default task statuses cannot be deleted', 422);
        }
        $status->delete();
        return ResponseHelper::success(null, 'Task status deleted successfully');
    }
}

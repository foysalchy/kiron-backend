<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectDiscussion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDiscussionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $taskId = $request->get('task_id');

        $query = ProjectDiscussion::with(['user:id,name', 'replies'])
            ->whereNull('parent_id');

        if ($projectId) $query->where('project_id', $projectId);
        if ($taskId) $query->where('task_id', $taskId);

        return ResponseHelper::success($query->latest()->get(), 'Discussions retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'parent_id' => 'nullable|exists:project_discussions,id',
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'mentions' => 'nullable|array',
        ]);

        $validated['user_id'] = Auth::id();
        $discussion = ProjectDiscussion::create($validated);

        return ResponseHelper::created($discussion->load(['user:id,name', 'replies']), 'Message posted');
    }

    public function destroy(int $id): JsonResponse
    {
        $discussion = ProjectDiscussion::findOrFail($id);
        $discussion->delete();
        return ResponseHelper::success(null, 'Discussion deleted');
    }
}

<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = ProjectDocument::with('uploader:id,name');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return ResponseHelper::success($query->latest()->get(), 'Documents retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'name' => 'required|string',
            'file_path' => 'required|string',
            'file_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'category' => 'nullable|string',
        ]);

        $validated['uploaded_by'] = Auth::id();
        $doc = ProjectDocument::create($validated);
        return ResponseHelper::created($doc->load('uploader:id,name'), 'Document added');
    }

    public function destroy(int $id): JsonResponse
    {
        $doc = ProjectDocument::findOrFail($id);
        $doc->delete();
        return ResponseHelper::success(null, 'Document deleted');
    }
}

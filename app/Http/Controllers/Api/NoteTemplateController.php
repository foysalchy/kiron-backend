<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteTemplateRequest;
use App\Http\Requests\UpdateNoteTemplateRequest;
use App\Services\NoteTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteTemplateController extends Controller
{
    public function __construct(
        protected NoteTemplateService $noteTemplateService
    ) {}

    /**
     * Display a listing of note templates.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->noteTemplateService->getAllTemplates($filters, true);

        return ResponseHelper::success($data, 'Note templates retrieved successfully');
    }

    /**
     * Store a newly created note template.
     */
    public function store(StoreNoteTemplateRequest $request): JsonResponse
    {
        $data = $this->noteTemplateService->createTemplate($request->validated());

        return ResponseHelper::success($data, 'Note template created successfully', 201);
    }

    /**
     * Display the specified note template.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->noteTemplateService->getTemplateById($id);

        return ResponseHelper::success($data, 'Note template retrieved successfully');
    }

    /**
     * Update the specified note template.
     */
    public function update(UpdateNoteTemplateRequest $request, int $id): JsonResponse
    {
        $data = $this->noteTemplateService->updateTemplate($id, $request->validated());

        return ResponseHelper::success($data, 'Note template updated successfully');
    }

    /**
     * Remove the specified note template (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->noteTemplateService->deleteTemplate($id);

        return ResponseHelper::success(null, 'Note template deleted successfully');
    }

    /**
     * Restore a soft-deleted note template.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->noteTemplateService->restoreTemplate($id);

        return ResponseHelper::success($data, 'Note template restored successfully');
    }
     /**
     * Permanently delete .
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->noteTemplateService->forceDeleteTemplate($id);

        return ResponseHelper::success(null, 'Office location permanently deleted');
    }

    /**
     * Toggle note template status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->noteTemplateService->toggleStatus($id);

        return ResponseHelper::success($data, 'Note template status updated successfully');
    }
}

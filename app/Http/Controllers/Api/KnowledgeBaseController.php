<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeBaseRequest;
use App\Http\Requests\UpdateKnowledgeBaseRequest;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function __construct(
        protected KnowledgeBaseService $knowledgeBaseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'select'     => $request->query('select'),
            'with'       => $request->query('with'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        
        $data = $this->knowledgeBaseService->getAllKnowledgeBases($filters);
        
        return ResponseHelper::success($data, 'Knowledge base retrieved successfully');
    }

    public function store(StoreKnowledgeBaseRequest $request): JsonResponse
    {
        $data = $this->knowledgeBaseService->createKnowledgeBase($request->validated());

        return ResponseHelper::success($data, 'Knowledge base created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->knowledgeBaseService->getKnowledgeBaseById($id);

        return ResponseHelper::success($data, 'Knowledge base retrieved successfully');
    }

    public function update(UpdateKnowledgeBaseRequest $request, int $id): JsonResponse
    {
        $data = $this->knowledgeBaseService->updateKnowledgeBase($id, $request->validated());

        return ResponseHelper::success($data, 'Knowledge base updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->knowledgeBaseService->deleteKnowledgeBase($id);
        
        return ResponseHelper::success(null, 'Knowledge base deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->knowledgeBaseService->restoreKnowledgeBase($id);

        return ResponseHelper::success($data, 'Knowledge base restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->knowledgeBaseService->forceDeleteKnowledgeBase($id);

        return ResponseHelper::success(null, 'Knowledge base permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->knowledgeBaseService->toggleStatus($id);

        return ResponseHelper::success($data, 'Knowledge base status updated successfully');
    }
}
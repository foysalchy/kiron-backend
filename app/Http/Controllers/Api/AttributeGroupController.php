<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{AttributeGroupRequest, UpdateAttributeGroupRequest};
use App\Services\attributeGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
{
    public function __construct(
        protected AttributeGroupService $attributeGroupService
    ) {}


    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'status' => $request->query('status'),
            'category' => $request->query('category'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->attributeGroupService->getAllAttributeGroup($filters, true);

        return ResponseHelper::success($data, 'Attribute Group retrieved successfully');
    }
    public function getByCompany(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'status' => $request->query('status'),
            'category' => $request->query('category'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->attributeGroupService->getAttributeGroupByCompany($filters,$request->user()->company_id, true);

        return ResponseHelper::success($data, 'Attribute Group retrieved successfully');
    }


    public function store(AttributeGroupRequest $request): JsonResponse
    {
        $group = $this->attributeGroupService->createAttributeGroup($request->validated());

        return ResponseHelper::created($group, 'Attribute Group created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->attributeGroupService->getAttributeGroupById($id);

        return ResponseHelper::success($data, 'Attribute Group retrieved successfully');
    }


    public function update(UpdateAttributeGroupRequest $request, int $id): JsonResponse
    {
        $data = $this->attributeGroupService->updateAttributeGroup($id, $request->validated());

        return ResponseHelper::success($data, 'Attribute Group updated successfully');
    }


    public function destroy(int $id): JsonResponse
    {
        $this->attributeGroupService->deleteAttributeGroup($id);

        return ResponseHelper::success(null, 'Attribute Group deleted successfully');
    }


    public function restore(int $id): JsonResponse
    {
        $data = $this->attributeGroupService->restoreAttributeGroup($id);

        return ResponseHelper::success($data, 'Attribute Group restored successfully');
    }


    public function forceDestroy(int $id): JsonResponse
    {
        $this->attributeGroupService->forceDeleteAttributeGroup($id);

        return ResponseHelper::success(null, 'Attribute Group permanently deleted');
    }

    /**
     * Toggle Attribute Group status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->attributeGroupService->toggleStatus($id);

        return ResponseHelper::success($data, 'Attribute Group status updated successfully');
    }

    
}

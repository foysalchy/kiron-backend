<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{ StoreAttributeRequest, UpdateAttributeRequest};
use App\Exceptions\ApiException;
use App\Services\AttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AttributeValueController extends Controller
{
    public function __construct(
        protected AttributeService $attributeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'attribute_group_id' => $request->query('attribute_group_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->attributeService->getAllAttributes($filters, true);

        return ResponseHelper::success($data, 'Attributes retrieved successfully');
    }

    public function store(StoreAttributeRequest $request): JsonResponse
    {
        $data = $this->attributeService->createAttribute($request->validated());

        return ResponseHelper::success($data, 'Attribute created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->attributeService->getAttributeById($id);

        return ResponseHelper::success($data, 'Attribute retrieved successfully');
    }

    public function update(UpdateAttributeRequest $request, int $id): JsonResponse
    {
        $data = $this->attributeService->updateAttribute($id, $request->validated());

        return ResponseHelper::success($data, 'Attributes updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->attributeService->deleteAttribute($id);
        return ResponseHelper::success(null, 'Attribute deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->attributeService->restoreAttribute($id);

        return ResponseHelper::success($data, 'Attribute restore successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->attributeService->forceDeleteAttribute($id);

        return ResponseHelper::success(null, 'Attribute permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->attributeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Attribute status updated successfully');
    }

   

   

 
}

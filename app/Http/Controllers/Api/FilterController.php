<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\UpdateFilterRequest;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function __construct(
        protected FilterService $filterService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $columns = ['*'];
        if ($request->has('select')) {
            $select = $request->query('select');
            $columns = is_string($select) ? explode(',', $select) : $select;
        }

        $data = $this->filterService->getAllFilters($filters, true, $columns);

        return ResponseHelper::success($data, 'Filters retrieved successfully');
    }

    public function store(FilterRequest $request): JsonResponse
    {
        $filter = $this->filterService->createFilter($request->validated());

        return ResponseHelper::created($filter, 'Filter created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->filterService->getFilterById($id);

        return ResponseHelper::success($data, 'Filter retrieved successfully');
    }

    public function update(UpdateFilterRequest $request, int $id): JsonResponse
    {
        $data = $this->filterService->updateFilter($id, $request->validated());

        return ResponseHelper::success($data, 'Filter updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->filterService->deleteFilter($id);

        return ResponseHelper::success(null, 'Filter deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->filterService->restoreFilter($id);

        return ResponseHelper::success($data, 'Filter restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->filterService->forceDeleteFilter($id);

        return ResponseHelper::success(null, 'Filter permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->filterService->toggleStatus($id);

        return ResponseHelper::success($data, 'Filter status updated successfully');
    }

    public function addValues(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'names' => ['required', 'array'],
            'names.*' => ['required', 'string', 'max:255'],
        ]);

        $data = $this->filterService->addFilterValues($id, $request->only('names'));

        return ResponseHelper::success($data, 'Filter values added successfully');
    }
}


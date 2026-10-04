<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Services\AreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct(protected AreaService $areaService)
    {}

    public function index(Request $request):JsonResponse
    {
        $filters = [
            'warehouse_id' => $request->query('warehouse_id'),
            'select'     => $request->query('select'),
            'with'       => $request->query('with'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->areaService->getAllAreas($filters);
        return ResponseHelper::success($data,'Area retrieved successfully');
    }
    public function store(StoreAreaRequest $request): JsonResponse
    {
        $data = $this->areaService->createArea($request->validated());

        $token = $request->bearerToken();

        return ResponseHelper::success([
            'area' => $data,
            'token'     => $token],
             'Area created successfully');
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->areaService->getAreaById($id);

        return ResponseHelper::success($data, 'Area retrieved successfully');
    }
    public function update(UpdateAreaRequest $request, int $id): JsonResponse
    {
        $data = $this->areaService->updateArea($id, $request->validated());

        return ResponseHelper::success($data, 'Area updated successfully');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->areaService->deleteArea($id);
        return ResponseHelper::success(null, 'Area deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->areaService->restoreArea($id);

        return ResponseHelper::success($data, 'Area restore successfully');
    }
    public function forceDestroy(int $id): JsonResponse
    {
        $this->areaService->forceDeleteArea($id);

        return ResponseHelper::success(null, 'Area permanently deleted');
    }
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->areaService->toggleStatus($id);

        return ResponseHelper::success($data, 'Area status updated successfully');
    }
}



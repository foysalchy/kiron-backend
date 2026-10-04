<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRackRequest;
use App\Http\Requests\UpdateRackRequest;
use App\Models\Rack;
use App\Services\RackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function __construct(protected RackService $rackService)
    {

    }
    public function index(Request $request):JsonResponse
    {
        $filters = [
            'area_id' => $request->query('area_id'),
            'select'     => $request->query('select'),
            'with'       => $request->query('with'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->rackService->getAllRacks($filters);
        return ResponseHelper::success($data,'Rack retrieved successfully');
    }
    public function store(StoreRackRequest $request): JsonResponse
    {
        $data = $this->rackService->createRack($request->validated());


        return ResponseHelper::success([
            'rack' => $data,
        ], 'Rack created successfully');
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->rackService->getRackById($id);

        return ResponseHelper::success($data, 'Rack retrieved successfully');
    }
    public function update(UpdateRackRequest $request, int $id): JsonResponse
    {
        $data = $this->rackService->updateRack($id, $request->validated());

        return ResponseHelper::success($data, 'Rack updated successfully');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->rackService->deleteRack($id);
        return ResponseHelper::success(null, 'Rack deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->rackService->restoreRack($id);

        return ResponseHelper::success($data, 'Rack restore successfully');
    }
    public function forceDestroy(int $id): JsonResponse
    {
        $this->rackService->forceDeleteRack($id);

        return ResponseHelper::success(null, 'Rack permanently deleted');
    }
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->rackService->toggleStatus($id);

        return ResponseHelper::success($data, 'Rack status updated successfully');
    }
}



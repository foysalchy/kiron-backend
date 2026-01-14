<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCellRequest;
use App\Http\Requests\UpdateCellRequest;
use App\Services\CellService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CellController extends Controller
{
    public function __construct(protected CellService $cellService)
    {

    }
    public function index(Request $request):JsonResponse
    {
        $filters = [
            'rack_id' => $request->query('rack_id'),
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->cellService->getAllCells($filters);
        return ResponseHelper::success($data,'Cell retrieved successfully');
    }
    public function store(StoreCellRequest $request): JsonResponse
    {
        $data = $this->cellService->createCell($request->validated());

        $token = $request->bearerToken();

        return ResponseHelper::success([
            'cell' => $data,
            'token'     => $token],
             'Cell created successfully');
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->cellService->getCellById($id);

        return ResponseHelper::success($data, 'Cell retrieved successfully');
    }
    public function update(UpdateCellRequest $request, int $id): JsonResponse
    {
        $data = $this->cellService->updateCell($id, $request->validated());

        return ResponseHelper::success($data, 'Cell updated successfully');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->cellService->deleteCell($id);
        return ResponseHelper::success(null, 'Cell deleted successfully');
    }
    public function restore(int $id): JsonResponse
    {
        $data = $this->cellService->restoreCell($id);

        return ResponseHelper::success($data, 'Cell restore successfully');
    }
    public function forceDestroy(int $id): JsonResponse
    {
        $this->cellService->forceDeleteCell($id);

        return ResponseHelper::success(null, 'Cell permanently deleted');
    }
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->cellService->toggleStatus($id);

        return ResponseHelper::success($data, 'Cell status updated successfully');
    }
}

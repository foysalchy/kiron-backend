<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function __construct(protected TableService $tableService)
    {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'is_active'  => $request->query('is_active'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->tableService->getAllTables($filters);
        return ResponseHelper::success($data, 'Tables retrieved successfully');
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $data = $this->tableService->createTable($request->validated());
        return ResponseHelper::success($data, 'Table created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->tableService->getTableById($id);
        return ResponseHelper::success($data, 'Table retrieved successfully');
    }

    public function update(UpdateTableRequest $request, int $id): JsonResponse
    {
        $data = $this->tableService->updateTable($id, $request->validated());
        return ResponseHelper::success($data, 'Table updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tableService->deleteTable($id);
        return ResponseHelper::success(null, 'Table deleted successfully');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->tableService->toggleStatus($id);
        return ResponseHelper::success($data, 'Table status updated successfully');
    }
}


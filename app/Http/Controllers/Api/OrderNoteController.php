<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderNoteRequest;
use App\Http\Requests\UpdateOrderNoteRequest;
use App\Services\OrderNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderNoteController extends Controller
{
    public function __construct(protected OrderNoteService $orderNoteService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [

            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->orderNoteService->getAllOrderNote($filters);
        return ResponseHelper::success($data, 'Order Note retrieved successfully');
    }
    public function store(StoreOrderNoteRequest $request): JsonResponse
    {
        $data = $this->orderNoteService->createOrderNote($request->validated());

        return  ResponseHelper::success($data, 'Order note created successfully', 201);
    }
    public function show(int $id): JsonResponse
    {
        $data = $this->orderNoteService->getOrderNoteById($id);

        return ResponseHelper::success($data, 'Order Note retrieved successfully');
    }
    public function update(UpdateOrderNoteRequest $request, int $id): JsonResponse
    {
        $data = $this->orderNoteService->updateOrderNote($id, $request->validated());

        return ResponseHelper::success($data, 'Area updated successfully');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->orderNoteService->deleteOrderNote($id);
        return ResponseHelper::success(null, 'Area deleted successfully');
    }
}

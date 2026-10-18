<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use App\Services\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService
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

        $data = $this->noticeService->getAllNotices($filters);

        return ResponseHelper::success($data, 'Notices retrieved successfully');
    }

    public function store(StoreNoticeRequest $request): JsonResponse
    {
        $data = $this->noticeService->createNotice($request->validated());

        return ResponseHelper::success($data, 'Notice created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->noticeService->getNoticeById($id);

        return ResponseHelper::success($data, 'Notice retrieved successfully');
    }

    public function update(UpdateNoticeRequest $request, int $id): JsonResponse
    {
        $data = $this->noticeService->updateNotice($id, $request->validated());

        return ResponseHelper::success($data, 'Notice updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->noticeService->deleteNotice($id);

        return ResponseHelper::success(null, 'Notice deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->noticeService->restoreNotice($id);

        return ResponseHelper::success($data, 'Notice restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->noticeService->forceDeleteNotice($id);

        return ResponseHelper::success(null, 'Notice permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->noticeService->toggleStatus($id);

        return ResponseHelper::success($data, 'Notice status updated successfully');
    }
}

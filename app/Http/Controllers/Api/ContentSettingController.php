<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderContentSettingRequest;
use App\Http\Requests\StoreContentSettingRequest;
use App\Http\Requests\UpdateContentSettingRequest;
use App\Services\ContentSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentSettingController extends Controller
{
    public function __construct(
        private readonly ContentSettingService $service
    ) {}

    /* ────────────────────────────────────────────────────────
     |  GET /api/v1/content-settings
     |  Optional query param: ?page_type=product_page
     |──────────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page_type' => ['nullable', 'in:product_page,checkout_page,all_page,cart_page'],
        ]);

        $data = $this->service->getAll($request->query('page_type'));

        return response()->json([
            'success' => true,
            'message' => 'Content settings fetched successfully.',
            'data'    => $data,
        ]);
    }

    /* ────────────────────────────────────────────────────────
     |  GET /api/v1/content-settings/{id}
     |──────────────────────────────────────────────────────── */
    public function show(int $id): JsonResponse
    {
        $setting = $this->service->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Content setting fetched successfully.',
            'data'    => $setting,
        ]);
    }

    /* ────────────────────────────────────────────────────────
     |  POST /api/v1/content-settings
     |──────────────────────────────────────────────────────── */
    public function store(StoreContentSettingRequest $request): JsonResponse
    {
        $setting = $this->service->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Content setting created successfully.',
            'data'    => $setting,
        ], 201);
    }

    /* ────────────────────────────────────────────────────────
     |  PUT /api/v1/content-settings/{id}
     |──────────────────────────────────────────────────────── */
    public function update(UpdateContentSettingRequest $request, int $id): JsonResponse
    {
        $setting = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Content setting updated successfully.',
            'data'    => $setting,
        ]);
    }

    /* ────────────────────────────────────────────────────────
     |  PATCH /api/v1/content-settings/{id}/toggle-status
     |──────────────────────────────────────────────────────── */
    public function toggleStatus(int $id): JsonResponse
    {
        $setting = $this->service->toggleStatus($id);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data'    => $setting,
        ]);
    }

    /* ────────────────────────────────────────────────────────
     |  PATCH /api/v1/content-settings/reorder
     |  Body: { items: [{ id: 1, sort_order: 0 }, ...] }
     |──────────────────────────────────────────────────────── */
    public function reorder(ReorderContentSettingRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('items'));

        return response()->json([
            'success' => true,
            'message' => 'Items reordered successfully.',
        ]);
    }

    /* ────────────────────────────────────────────────────────
     |  DELETE /api/v1/content-settings/{id}
     |──────────────────────────────────────────────────────── */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Content setting deleted successfully.',
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api\Saas;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\StoreMasterDemoRequest;
use App\Http\Requests\Saas\UpdateMasterDemoRequest;
use App\Services\Saas\MasterDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDemoController extends Controller
{
    public function __construct(protected MasterDemoService $demoService)
    {}

    /**
     * list all master demos with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->demoService->getAllDemos($filters);
        return ResponseHelper::success($data, 'Master demos retrieved successfully');
    }

    /**
     * create a new master demo
     */
    public function store(StoreMasterDemoRequest $request): JsonResponse
    {
        $data = $this->demoService->createDemo($request->validated());
        return ResponseHelper::success($data, 'Master demo created successfully');
    }

    /**
     * show a specific master demo
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->demoService->getDemoById($id);
        return ResponseHelper::success($data, 'Master demo details retrieved');
    }

    /**
     * update a specific master demo
     */
    public function update(UpdateMasterDemoRequest $request, int $id): JsonResponse
    {
        \Log::info($request);
        $data = $this->demoService->updateDemo($id, $request->validated());
        return ResponseHelper::success($data, 'Master demo updated successfully');
    }

    /**
     * soft delete a master demo
     */
    public function destroy(int $id): JsonResponse
    {
        $this->demoService->deleteDemo($id);
        return ResponseHelper::success(null, 'Master demo deleted successfully');
    }

    /**
     * restore a soft deleted demo
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->demoService->restoreDemo($id);
        return ResponseHelper::success($data, 'Master demo restored successfully');
    }

    /**
     * permanent delete
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->demoService->forceDeleteDemo($id);
        return ResponseHelper::success(null, 'Master demo permanently deleted');
    }

    /**
     * toggle status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->demoService->toggleStatus($id);
        return ResponseHelper::success($data, 'Master demo status updated successfully');
    }
}

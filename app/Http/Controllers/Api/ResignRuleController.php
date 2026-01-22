<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResignRuleRequest;
use App\Http\Requests\UpdateResignRuleRequest;
use App\Services\ResignRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResignRuleController extends Controller
{
    public function __construct(
        protected ResignRuleService $resignRuleService)
    {}
    public function index(Request $request)
    {
        $filters = [
            'search'     => $request->query('search'),
            'status'     => $request->query('status'),
            'sort_by'    => $request->query('sort_by', 'name'),
            'sort_order' => $request->query('sort_order', 'asc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->resignRuleService->getAllResignRules($filters, true);

        return ResponseHelper::success($data, 'Resign Rules retrieved successfully');
    }
    /**
     * Store a newly created resign rule.
     */
    public function store(StoreResignRuleRequest $request): JsonResponse
    {
        $rule = $this->resignRuleService->createResignRule($request->validated());

        return ResponseHelper::created($rule, 'Resign Rule created successfully');
    }

    /**
     * Display the specified resign rule.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->resignRuleService->getResignRuleById($id);

        return ResponseHelper::success($data, 'Resign Rule retrieved successfully');
    }

    /**
     * Update the specified resign rule.
     */
    public function update(UpdateResignRuleRequest $request, int $id): JsonResponse
    {
        $data = $this->resignRuleService->updateResignRule($id, $request->validated());

        return ResponseHelper::success($data, 'Resign Rule updated successfully');
    }

    /**
     * Remove the specified resign rule (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->resignRuleService->deleteResignRule($id);

        return ResponseHelper::success(null, 'Resign Rule deleted successfully');
    }

    /**
     * Restore the soft deleted resign rule.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->resignRuleService->restoreResignRule($id);

        return ResponseHelper::success($data, 'Resign Rule restored successfully');
    }

    /**
     * Permanently delete the resign rule.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->resignRuleService->forceDeleteResignRule($id);

        return ResponseHelper::success(null, 'Resign Rule permanently deleted');
    }

    /**
     * Toggle resign rule status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->resignRuleService->toggleStatus($id);

        return ResponseHelper::success($data, 'Resign Rule status updated successfully');
    }
}

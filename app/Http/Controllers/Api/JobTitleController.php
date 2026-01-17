<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Services\JobTitleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function __construct(
        protected JobTitleService $jobTitleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->jobTitleService->getAllJobTitles($filters, true);

        return ResponseHelper::success($data, 'Job titles retrieved successfully');
    }

    public function store(StoreJobTitleRequest $request): JsonResponse
    {
        $data = $this->jobTitleService->createJobTitle($request->validated());

        return ResponseHelper::success($data, 'Job title created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->jobTitleService->getJobTitleById($id);

        return ResponseHelper::success($data, 'Job title retrieved successfully');
    }

    public function update(UpdateJobTitleRequest $request, int $id): JsonResponse
    {
        $data = $this->jobTitleService->updateJobTitle($id, $request->validated());

        return ResponseHelper::success($data, 'Job title updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->jobTitleService->deleteJobTitle($id);

        return ResponseHelper::success(null, 'Job title deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $data = $this->jobTitleService->restoreJobTitle($id);

        return ResponseHelper::success($data, 'Job title restored successfully');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $this->jobTitleService->forceDeleteJobTitle($id);

        return ResponseHelper::success(null, 'Job title permanently deleted');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->jobTitleService->toggleStatus($id);

        return ResponseHelper::success($data, 'Job title status updated successfully');
    }
}

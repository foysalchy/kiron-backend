<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainSetupRequest;
use App\Services\DomainSetupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainSetupController extends Controller
{
    public function __construct(protected DomainSetupService $domainService) {}

    public function index(): JsonResponse
    {
        $data = $this->domainService->getDomain();
        return ResponseHelper::success($data, 'Domain info retrieved successfully');
    }
    public function domains(): JsonResponse
    {
        $data = $this->domainService->multiDomain();
        return ResponseHelper::success($data, 'Domain info retrieved successfully');
    }
    public function deleteDomain($id): JsonResponse
    {
        $data = $this->domainService->deleteDomain($id);
        return ResponseHelper::success(null, 'Domain delete successfully');
    }

    public function store(UpdateDomainSetupRequest $request): JsonResponse
    {
        // dd($request);
        $result = $this->domainService->saveDomain($request->validated());
        if (is_string($result)) {
            return response()->json([
                'success' => false,
                'message' => $result,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Domain settings updated successfully',
            'data'    => $result,
        ]);
    }
    public function multiDomain(StoreDomainRequest $request): JsonResponse
    {
        // dd($request);
        $result = $this->domainService->saveMultiDomain($request->only('domain'));

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => 'Domain store successfully',
            'data'    => $result['data'],
        ], 201);
    }
}

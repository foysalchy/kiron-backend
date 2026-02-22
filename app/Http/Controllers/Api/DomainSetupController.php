<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
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


}

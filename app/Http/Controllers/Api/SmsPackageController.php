<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsPackageRequest;
use App\Services\SmsPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsPackageController extends Controller
{
    public function __construct(private SmsPackageService $service) {}

    public function index(): JsonResponse
    {
        return ResponseHelper::success($this->service->getAll());
    }

    public function store(StoreSmsPackageRequest $request): JsonResponse
    {
        $package = $this->service->store($request->validated());
        return ResponseHelper::success($package, 'Package created successfully', 201);
    }

    public function update(StoreSmsPackageRequest $request, int $id): JsonResponse
    {
        $package = $this->service->update($id, $request->validated());
        return ResponseHelper::success($package, 'Package updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return ResponseHelper::success(null, 'Package deleted successfully');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageUpgradeRequest;
use App\Services\PackageUpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageUpgradeController extends Controller
{
    public function __construct(protected PackageUpgradeService $upgradeService) {}

    /**
     * Index list
     */
    public function index(Request $request): JsonResponse
    {
        
        $filters = [
            'pricing_package_id' => $request->query('pricing_package_id'),
            'status'             => $request->query('status'),
            'sort_by'            => $request->query('sort_by', 'created_at'),
            'sort_order'         => $request->query('sort_order', 'desc'),
            'per_page'           => $request->query('per_page', 15),
        ];

        $data = $this->upgradeService->getAllUpgradeRequests($filters);
        return ResponseHelper::success($data, 'Package upgrade requests retrieved successfully');
    }

    /**
     * Store new request
     */
    public function store(StorePackageUpgradeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // File storage handling
        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('documents/upgrades', 'public');
        }

        $data = $this->upgradeService->createUpgradeRequest($data);

        return ResponseHelper::success($data, 'Package upgrade request successfully submitted');
    }

    /**
     * Update Status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $data = $this->upgradeService->updateStatus($id, $request->input('status'));

        return ResponseHelper::success($data, 'Upgrade request status updated successfully');
    }
}

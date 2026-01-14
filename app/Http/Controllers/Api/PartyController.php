<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StorePartyRequest, UpdatePartyRequest, UpdateBalanceRequest};
use App\Services\PartyService;
use App\Exceptions\ApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function __construct(
        protected PartyService $partyService
    ) {}


    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'type' => $request->query('type'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $parties = $this->partyService->getAllParties($filters, true);

        return ResponseHelper::success($parties, 'Parties retrieved successfully');
    }
    public function getByCompany(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'type' => $request->query('type'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $parties = $this->partyService->getParyByCompany($filters,$request->user()->company_id, true);

        return ResponseHelper::success($parties, 'Parties retrieved successfully');
    }


    public function store(StorePartyRequest $request): JsonResponse
    {
        $party = $this->partyService->createParty($request->validated());

        return ResponseHelper::created($party, 'Party created successfully');
    }


    public function show(int $id): JsonResponse
    {
        $party = $this->partyService->getPartyById($id);

        return ResponseHelper::success($party, 'Party retrieved successfully');
    }


    public function update(UpdatePartyRequest $request, int $id): JsonResponse
    {
        $party = $this->partyService->updateParty($id, $request->validated());

        return ResponseHelper::success($party, 'Party updated successfully');
    }


    public function destroy(int $id): JsonResponse
    {
        $this->partyService->deleteParty($id);

        return ResponseHelper::success(null, 'Party deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $party = $this->partyService->restoreParty($id);

        return ResponseHelper::success($party, 'Party restored successfully');
    }


    public function forceDestroy(int $id): JsonResponse
    {
        $this->partyService->forceDeleteParty($id);

        return ResponseHelper::success(null, 'Party permanently deleted');
    }


    public function toggleStatus(int $id): JsonResponse
    {
        $party = $this->partyService->toggleStatus($id);

        return ResponseHelper::success($party, 'Party status updated successfully');
    }


    // Update party balance

    public function updateBalance(UpdateBalanceRequest $request, int $id): JsonResponse
    {
        $party = $this->partyService->updateBalance($id, [
            'amount' => $request->input('amount'),
            'type'   => $request->input('type', 'add'),
        ]);

        return ResponseHelper::success($party, 'Balance updated successfully');
    }

    /**
     * Get suppliers by company
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        if (!$companyId) {
            throw ApiException::badRequest('Company ID is required');
        }

        $suppliers = $this->partyService->getSuppliers($companyId);

        return ResponseHelper::success($suppliers, 'Suppliers retrieved successfully');
    }

    /**
     * Get customers by company
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        if (!$companyId) {
            throw ApiException::badRequest('Company ID is required');
        }

        $customers = $this->partyService->getCustomers($companyId);

        return ResponseHelper::success($customers, 'Customers retrieved successfully');
    }

    /**
     * Search parties
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->query('term');

        if (!$term) {
            throw ApiException::badRequest('Search term is required');
        }

        $parties = $this->partyService->searchParties(
            $term,
            $request->query('company_id'),
            $request->query('type')
        );

        return ResponseHelper::success($parties, 'Search results retrieved successfully');
    }
}

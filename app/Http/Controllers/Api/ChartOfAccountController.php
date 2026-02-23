<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreChartOfAccountRequest;
use App\Http\Requests\Accounts\UpdateChartOfAccountRequest;
use App\Services\ChartOfAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function __construct(protected ChartOfAccountService $chartOfAccountService) {}

    /**
     * Display a listing of chart of accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'           => $request->query('status'),
            'account_group_id' => $request->query('account_group_id'),
            'search'           => $request->query('search'),
            'sort_by'         => $request->query('sort_by', 'created_at'),
            'sort_order'      => $request->query('sort_order', 'desc'),
            'per_page'         => $request->query('per_page', 15),
        ];

        $data = $this->chartOfAccountService->getAllAccounts($filters, true);

        return ResponseHelper::success($data, 'Chart of accounts retrieved successfully');
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreChartOfAccountRequest $request): JsonResponse
    {
        $data = $this->chartOfAccountService->createAccount($request->validated());

        return ResponseHelper::success($data, 'Account created successfully', 201);
    }

    /**
     * Display the specified account.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->chartOfAccountService->getAccountById($id);

        return ResponseHelper::success($data, 'Account retrieved successfully');
    }

    /**
     * Update the specified account.
     */
    public function update(UpdateChartOfAccountRequest $request, int $id): JsonResponse
    {
        $data = $this->chartOfAccountService->updateAccount($id, $request->validated());

        return ResponseHelper::success($data, 'Account updated successfully');
    }

    /**
     * Remove the specified account (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->chartOfAccountService->deleteAccount($id);

        return ResponseHelper::success(null, 'Account deleted successfully');
    }
     /**
     * Restore soft-deleted .
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->chartOfAccountService->restoreAccount($id);

        return ResponseHelper::success($data, 'Account restored successfully');
    }
    /**
     * Permanently delete .
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->chartOfAccountService->forceDeleteAccount($id);

        return ResponseHelper::success(null, 'Account permanently deleted');
    }

    /**
     * Toggle status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->chartOfAccountService->toggleStatus($id);

        return ResponseHelper::success($data, 'Account status updated successfully');
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(protected CurrencyService $currencyService)
    {}
    /**
     * Display a listing of the currencies.
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

        $data = $this->currencyService->getAllCurrencies($filters, true);

        return ResponseHelper::success($data, 'Currencies retrieved successfully');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $data = $this->currencyService->createCurrency($request->validated());

        return ResponseHelper::success($data, 'Currency created successfully', 201);
    }

    /**
     * Display the specified currency.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->currencyService->getCurrencyById($id);

        return ResponseHelper::success($data, 'Currency retrieved successfully');
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(UpdateCurrencyRequest $request, int $id): JsonResponse
    {
        $data = $this->currencyService->updateCurrency($id, $request->validated());

        return ResponseHelper::success($data, 'Currency updated successfully');
    }

    /**
     * Remove the specified currency from storage (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->currencyService->deleteCurrency($id);

        return ResponseHelper::success(null, 'Currency deleted successfully');
    }

    /**
     * Restore the specified soft-deleted currency.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->currencyService->restoreCurrency($id);

        return ResponseHelper::success($data, 'Currency restored successfully');
    }

    /**
     * Permanently delete the specified currency.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->currencyService->forceDeleteCurrency($id);

        return ResponseHelper::success(null, 'Currency permanently deleted');
    }

    /**
     * Toggle the status of the specified currency.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->currencyService->toggleStatus($id);

        return ResponseHelper::success($data, 'Currency status updated successfully');
    }
}

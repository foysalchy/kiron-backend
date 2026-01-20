<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayRollPayHeadRequest;
use App\Http\Requests\UpdatePayRollPayHeadRequest;
use App\Services\PayRollPayHeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayRollPayHeadController extends Controller
{
    public function __construct(
        protected PayRollPayHeadService $service
    ) {}

    /**
     * Display a listing of assigned pay heads with calculations.
     */
    public function index(Request $request): JsonResponse
    {
        $payRollId = $request->query('pay_roll_id');

        if (!$payRollId) {
            return ResponseHelper::error('Payroll ID is required', 422);
        }

        $summary = $this->service->getPayRollSummary($payRollId);

        return ResponseHelper::success($summary, 'Payroll summary retrieved successfully');
    }

    /**
     * Store a newly assigned pay head.
     */
    public function store(StorePayRollPayHeadRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());

        return ResponseHelper::created($record, 'Pay head assigned successfully');
    }

    /**
     * Display the specified record.
     */
    public function show(int $id): JsonResponse
    {
        $record = $this->service->getById($id);

        return ResponseHelper::success($record, 'Record retrieved successfully');
    }

    /**
     * Update the assigned amount or type.
     */
    public function update(UpdatePayRollPayHeadRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());

        return ResponseHelper::success($record, 'Record updated successfully');
    }

    /**
     * Remove the pay head from payroll (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return ResponseHelper::success(null, 'Pay head removed from payroll');
    }

    /**
     * Restore a soft deleted pay head assignment.
     */
    public function restore(int $id): JsonResponse
    {
        $record = $this->service->restore($id);

        return ResponseHelper::success($record, 'Record restored successfully');
    }

    /**
     * Permanently delete a record.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->service->forceDelete($id);

        return ResponseHelper::success(null, 'Record permanently deleted');
    }
}

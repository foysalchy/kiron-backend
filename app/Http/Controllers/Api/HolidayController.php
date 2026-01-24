<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService
    ) {}

    /**
     * Display a listing of holidays.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'from_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->holidayService->getAllHolidays($filters, true);

        return ResponseHelper::success($data, 'Holidays retrieved successfully');
    }

    /**
     * Store a newly created holiday.
     */
    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $holiday = $this->holidayService->createHoliday($request->validated());

        return ResponseHelper::created($holiday, 'Holiday created successfully');
    }

    /**
     * Display the specified holiday.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->holidayService->getHolidayById($id);

        return ResponseHelper::success($data, 'Holiday retrieved successfully');
    }

    /**
     * Update the specified holiday.
     */
    public function update(UpdateHolidayRequest $request, int $id): JsonResponse
    {
        $data = $this->holidayService->updateHoliday($id, $request->validated());

        return ResponseHelper::success($data, 'Holiday updated successfully');
    }

    /**
     * Soft delete the holiday.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->holidayService->deleteHoliday($id);

        return ResponseHelper::success(null, 'Holiday deleted successfully');
    }

    /**
     * Restore a soft deleted holiday.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->holidayService->restoreHoliday($id);

        return ResponseHelper::success($data, 'Holiday restored successfully');
    }

    /**
     * Permanently delete the holiday.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->holidayService->forceDeleteHoliday($id);

        return ResponseHelper::success(null, 'Holiday permanently deleted');
    }

    /**
     * Toggle Holiday status (Active/Inactive)
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->holidayService->toggleStatus($id);

        return ResponseHelper::success($data, 'Holiday status updated successfully');
    }
}

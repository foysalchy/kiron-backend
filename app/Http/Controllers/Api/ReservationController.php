<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $reservationService)
    {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'table_id'   => $request->query('table_id'),
            'date'       => $request->query('date'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'reservation_date'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];
        $data = $this->reservationService->getAllReservations($filters);
        return ResponseHelper::success($data, 'Reservations retrieved successfully');
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $data = $this->reservationService->createReservation($request->validated());
        return ResponseHelper::success($data, 'Reservation created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->reservationService->getReservationById($id);
        return ResponseHelper::success($data, 'Reservation retrieved successfully');
    }

    public function update(UpdateReservationRequest $request, int $id): JsonResponse
    {
        $data = $this->reservationService->updateReservation($id, $request->validated());
        return ResponseHelper::success($data, 'Reservation updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->reservationService->deleteReservation($id);
        return ResponseHelper::success(null, 'Reservation deleted successfully');
    }

    public function updateStatus(\App\Http\Requests\UpdateReservationStatusRequest $request, int $id): JsonResponse
    {
        $reservation = $this->reservationService->getReservationById($id);
        $data = $this->reservationService->updateStatus($reservation, $request->validated()['status']);
        return ResponseHelper::success($data, 'Reservation status updated successfully');
    }
}


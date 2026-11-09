<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancellationReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $cancellationStatuses = [
            Status::Cancelled->value,
            Status::Fake->value,
            Status::Returned->value,
            Status::ReturntoCourier->value,
            Status::ReturnReceived->value,
            Status::ReturnRequest->value,
        ];

        $query = Order::query()
            ->whereIn('status', $cancellationStatuses);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $report = [];
        foreach ($orders as $order) {
            $statusEnum = $order->status instanceof Status ? $order->status : Status::tryFrom((int)$order->status);
            $statusLabel = $statusEnum ? $statusEnum->label() : 'Unknown';

            if (!isset($report[$statusLabel])) {
                $report[$statusLabel] = [
                    'status' => $statusLabel,
                    'count' => 0,
                    'lost_revenue' => 0,
                    'lost_courier_charge' => 0,
                ];
            }

            $report[$statusLabel]['count']++;
            $report[$statusLabel]['lost_revenue'] += $order->grand_total ?? $order->total_amount ?? 0;
            $report[$statusLabel]['lost_courier_charge'] += $order->other_charges ?? 0;
        }

        return ResponseHelper::success(array_values($report));
    }
}

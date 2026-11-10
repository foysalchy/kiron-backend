<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\Party;
use Illuminate\Support\Collection;

class CustomerReportService
{
    public function generate(array $filters): array
    {
        $startDate       = $filters['start_date'];
        $endDate         = $filters['end_date'];
        $repeatThreshold = (int) ($filters['repeat_threshold'] ?? 3);

        // ── 1. Date range orders (except Draft) ──
        $ordersInRange = Order::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', Status::Draft->value)
            ->select('id', 'customer_id', 'grand_total', 'order_date', 'status')
            ->get();

        $customerIdsInRange = $ordersInRange->pluck('customer_id')->filter()->unique();

        if ($customerIdsInRange->isEmpty()) {
            return $this->emptyResponse($startDate, $endDate, $repeatThreshold);
        }

        // ── 2. Customers who had orders before date range ──
        $returningCustomerIds = Order::where('order_date', '<', $startDate)
            ->whereIn('customer_id', $customerIdsInRange)
            ->where('status', '!=', Status::Draft->value)
            ->pluck('customer_id')
            ->unique();

        // ── 3. Customer details ──
        $customers = Party::whereIn('id', $customerIdsInRange)
            ->where('type', 2)
            ->select('id', 'name', 'phone', 'email', 'district')
            ->get()
            ->keyBy('id');

        // ── 4. Range orders group by customer ──
        $grouped = $ordersInRange->groupBy('customer_id');

        $newCustomers    = [];
        $repeatCustomers = [];
        $returnCustomers = [];

        foreach ($grouped as $customerId => $orders) {
            $orderCount    = $orders->count();
            $totalSpent    = (float) $orders->sum('grand_total');
            $avgOrderValue = $orderCount > 0 ? round($totalSpent / $orderCount, 2) : 0;
            $isReturn      = $returningCustomerIds->contains($customerId);
            $customer      = $customers->get($customerId);

            // Status breakdown calculation
            $statusBreakdown = [];
            foreach ($orders as $ord) {
                $statusEnum = $ord->status instanceof Status ? $ord->status : Status::tryFrom((int)$ord->status);
                $statusLabel = $statusEnum ? $statusEnum->label() : 'Unknown';
                $statusBreakdown[$statusLabel] = ($statusBreakdown[$statusLabel] ?? 0) + 1;
            }

            $row = [
                'customer_id'      => $customerId,
                'name'             => $customer?->name ?? 'Unknown',
                'phone'            => $customer?->phone ?? '-',
                'district'         => $customer?->district ?? '-',
                'order_count'      => $orderCount,
                'status_breakdown' => $statusBreakdown,
                'total_spent'      => round($totalSpent, 2),
                'avg_order_value'  => $avgOrderValue,
            ];

            // New = date range এ প্রথমবার order করেছে (আগে কোনো order ছিল না)
            if (!$isReturn && $orderCount > 0) {
                $newCustomers[] = $row;
            }

            // Repeat = date range এ >= threshold বার order
            if ($orderCount >= $repeatThreshold) {
                $repeatCustomers[] = $row;
            }

            // Return = আগে order ছিল, এবার আবার করেছে
            if ($isReturn) {
                $returnCustomers[] = $row;
            }
        }

        // ── 5. CLV — avg order value × lifetime order count ──
        $lifetimeOrders = Order::whereIn('customer_id', $customerIdsInRange)
            ->where('status', '!=', Status::Draft->value)
            ->select('id', 'customer_id', 'grand_total', 'status')
            ->get()
            ->groupBy('customer_id');

        $clvList = [];
        foreach ($lifetimeOrders as $cId => $allCustomerOrders) {
            $lifetimeCount = $allCustomerOrders->count();
            $lifetimeSpent = (float) $allCustomerOrders->sum('grand_total');
            $avgOrderValue = $lifetimeCount > 0 ? round($lifetimeSpent / $lifetimeCount, 2) : 0;
            $customer = $customers->get($cId);

            $lifetimeBreakdown = [];
            foreach ($allCustomerOrders as $cord) {
                $statusEnum = $cord->status instanceof Status ? $cord->status : Status::tryFrom((int)$cord->status);
                $statusLabel = $statusEnum ? $statusEnum->label() : 'Unknown';
                $lifetimeBreakdown[$statusLabel] = ($lifetimeBreakdown[$statusLabel] ?? 0) + 1;
            }

            $clvList[] = [
                'customer_id'      => $cId,
                'name'             => $customer?->name ?? 'Unknown',
                'phone'            => $customer?->phone ?? '-',
                'district'         => $customer?->district ?? '-',
                'lifetime_orders'  => $lifetimeCount,
                'status_breakdown' => $lifetimeBreakdown,
                'lifetime_spent'   => round($lifetimeSpent, 2),
                'avg_order_value'  => $avgOrderValue,
                'clv'              => round($lifetimeSpent, 2),
            ];
        }

        usort($clvList, fn($a, $b) => $b['clv'] <=> $a['clv']);

        $avgCLV = count($clvList) > 0
            ? round(array_sum(array_column($clvList, 'clv')) / count($clvList), 2)
            : 0;

        return [
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'filters'    => ['repeat_threshold' => $repeatThreshold],
            'summary'    => [
                'total_customers'  => $customerIdsInRange->count(),
                'new_customers'    => count($newCustomers),
                'repeat_customers' => count($repeatCustomers),
                'return_customers' => count($returnCustomers),
                'avg_clv'          => $avgCLV,
            ],
            'new_customers'    => $newCustomers,
            'repeat_customers' => $repeatCustomers,
            'return_customers' => $returnCustomers,
            'clv_data'         => $clvList,
        ];
    }

    private function emptyResponse(string $start, string $end, int $threshold): array
    {
        return [
            'date_range' => ['start' => $start, 'end' => $end],
            'filters'    => ['repeat_threshold' => $threshold],
            'summary'    => [
                'total_customers'  => 0,
                'new_customers'    => 0,
                'repeat_customers' => 0,
                'return_customers' => 0,
                'avg_clv'          => 0,
            ],
            'new_customers'    => [],
            'repeat_customers' => [],
            'return_customers' => [],
            'clv_data'         => [],
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\Party;
use Illuminate\Support\Collection;

class CustomerReportService
{
    private array $deliveredStatuses = [Status::Delivered->value];

    public function generate(array $filters): array
    {
        $startDate       = $filters['start_date'];
        $endDate         = $filters['end_date'];
        $repeatThreshold = (int) ($filters['repeat_threshold'] ?? 3);

        // ── 1. Date range  delivered orders ──
        $ordersInRange = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->select('id', 'customer_id', 'grand_total', 'order_date')
            ->get();

        $customerIdsInRange = $ordersInRange->pluck('customer_id')->unique();

        if ($customerIdsInRange->isEmpty()) {
            return $this->emptyResponse($startDate, $endDate, $repeatThreshold);
        }

        // ── 2. Date range এর আগে order আছে এমন customer_ids ──
        $returningCustomerIds = Order::where('order_date', '<', $startDate)
            ->whereIn('customer_id', $customerIdsInRange)
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('customer_id')
            ->unique();

        // ── 3. Customer details ──
        $customers = Party::whereIn('id', $customerIdsInRange)
            ->where('type', 2)
            ->select('id', 'name', 'phone', 'email', 'district')
            ->get()
            ->keyBy('id');

        // ── 4. Range এর orders group by customer ──
        $grouped = $ordersInRange->groupBy('customer_id');

        // ── New Customers — date range এ যাদের কোনো order নেই ──
        $customersWithOrdersInRange = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('customer_id')
            ->unique();

        // সব customer থেকে যাদের range এ order নেই
        $newCustomerIds = Party::where('type', 2)
            ->whereNotIn('id', $customersWithOrdersInRange)
            ->pluck('id');

        $newCustomers = Party::whereIn('id', $newCustomerIds)
            ->where('type', 2)
            ->select('id', 'name', 'phone', 'district')
            ->get()
            ->map(fn($c) => [
                'customer_id'     => $c->id,
                'name'            => $c->name,
                'phone'           => $c->phone,
                'district'        => $c->district ?? '-',
                'order_count'     => 0,
                'total_spent'     => 0,
                'avg_order_value' => 0,
            ])
            ->values()
            ->toArray();
        $repeatCustomers = [];
        $returnCustomers = [];

        foreach ($grouped as $customerId => $orders) {
            $orderCount    = $orders->count();
            $totalSpent    = (float) $orders->sum('grand_total');
            $avgOrderValue = $orderCount > 0 ? round($totalSpent / $orderCount, 2) : 0;
            $isReturn      = $returningCustomerIds->contains($customerId);
            $customer      = $customers->get($customerId);

            $row = [
                'customer_id'     => $customerId,
                'name'            => $customer?->name ?? 'Unknown',
                'phone'           => $customer?->phone ?? '-',
                'district'        => $customer?->district ?? '-',
                'order_count'     => $orderCount,
                'total_spent'     => round($totalSpent, 2),
                'avg_order_value' => $avgOrderValue,
            ];

            // New = date range এ প্রথমবার (আগে কোনো order নেই)
            if (!$isReturn) {
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
        $lifetimeData = Order::whereIn('customer_id', $customerIdsInRange)
            ->whereIn('status', $this->deliveredStatuses)
            ->selectRaw('customer_id, COUNT(*) as lifetime_orders, SUM(grand_total) as lifetime_spent')
            ->groupBy('customer_id')
            ->get();

        $clvList = $lifetimeData->map(function ($row) use ($customers) {
            $avgOrderValue = $row->lifetime_orders > 0
                ? $row->lifetime_spent / $row->lifetime_orders
                : 0;
            $clv      = round($avgOrderValue * $row->lifetime_orders, 2); // = lifetime_spent
            $customer = $customers->get($row->customer_id);

            return [
                'customer_id'     => $row->customer_id,
                'name'            => $customer?->name ?? 'Unknown',
                'phone'           => $customer?->phone ?? '-',
                'district'        => $customer?->district ?? '-',
                'lifetime_orders' => $row->lifetime_orders,
                'lifetime_spent'  => round($row->lifetime_spent, 2),
                'avg_order_value' => round($avgOrderValue, 2),
                'clv'             => $clv,
            ];
        })->sortByDesc('clv')->values()->toArray();

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

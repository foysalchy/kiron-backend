<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpWiseOrderReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'search'     => ['nullable', 'string', 'max:100'],
            'min_orders' => ['nullable', 'integer', 'min:1'],
        ]);

        $companyId = auth()->user()->company_id ?? null;

        $query = Order::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where(function ($q) {
                $q->whereNotNull('source_info')
                  ->orWhereNotNull('pixel_source_info');
            })
            ->with(['customer:id,name,phone'])
            ->select('id', 'company_id', 'customer_id', 'order_no', 'grand_total', 'status', 'source_info', 'pixel_source_info', 'created_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $ipDirectories = \App\Models\IpDirectory::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->get(['id', 'ip_address', 'status'])
            ->keyBy('ip_address');

        $ipGroups = [];
        $totalOrdersCount = 0;
        $totalSalesAmount = 0;

        foreach ($orders as $order) {
            $sourceInfo = is_array($order->source_info)
                ? $order->source_info
                : (is_string($order->source_info) ? json_decode($order->source_info, true) : null);

            $pixelInfo = is_array($order->pixel_source_info)
                ? $order->pixel_source_info
                : (is_string($order->pixel_source_info) ? json_decode($order->pixel_source_info, true) : null);

            $ip = $sourceInfo['ip'] ?? $pixelInfo['ip'] ?? null;
            if (!$ip || trim($ip) === '') {
                continue;
            }

            $ip = trim($ip);

            if (!isset($ipGroups[$ip])) {
                $ipDir = $ipDirectories->get($ip);
                $isBlocked = $ipDir ? ((int) $ipDir->status === 0) : false;

                $ipGroups[$ip] = [
                    'ip'               => $ip,
                    'is_blocked'       => $isBlocked,
                    'ip_directory_id'  => $ipDir?->id,
                    'total_orders'     => 0,
                    'total_amount'     => 0,
                    'customers'        => [],
                    'devices'          => [],
                    'platforms'        => [],
                    'browsers'         => [],
                    'statuses'         => [],
                    'first_order_date' => $order->created_at?->toIso8601String(),
                    'last_order_date'  => $order->created_at?->toIso8601String(),
                    'orders'           => [],
                ];
            }

            $ipGroups[$ip]['total_orders'] += 1;
            $ipGroups[$ip]['total_amount'] += (float) ($order->grand_total ?? 0);
            $totalOrdersCount += 1;
            $totalSalesAmount += (float) ($order->grand_total ?? 0);

            // Customer
            $cName = $order->customer?->name ?? 'Guest';
            $cPhone = $order->customer?->phone ?? '';
            $cKey = $cPhone ?: $cName;
            if (!isset($ipGroups[$ip]['customers'][$cKey])) {
                $ipGroups[$ip]['customers'][$cKey] = [
                    'name'  => $cName,
                    'phone' => $cPhone,
                ];
            }

            // Devices, Platforms, Browsers
            $device = $sourceInfo['device'] ?? null;
            if ($device && !in_array($device, $ipGroups[$ip]['devices'])) {
                $ipGroups[$ip]['devices'][] = $device;
            }

            $platform = $sourceInfo['platform'] ?? null;
            if ($platform && !in_array($platform, $ipGroups[$ip]['platforms'])) {
                $ipGroups[$ip]['platforms'][] = $platform;
            }

            $browser = $sourceInfo['browser'] ?? null;
            if ($browser && !in_array($browser, $ipGroups[$ip]['browsers'])) {
                $ipGroups[$ip]['browsers'][] = $browser;
            }

            // Status count
            $statusEnum = $order->status instanceof Status ? $order->status : Status::tryFrom((int) $order->status);
            $statusLabel = $statusEnum?->label() ?? (is_numeric($order->status) ? "Status {$order->status}" : ($order->status ?: 'Unknown'));
            $ipGroups[$ip]['statuses'][$statusLabel] = ($ipGroups[$ip]['statuses'][$statusLabel] ?? 0) + 1;

            // Dates
            if (!$ipGroups[$ip]['first_order_date'] || $order->created_at?->lt($ipGroups[$ip]['first_order_date'])) {
                $ipGroups[$ip]['first_order_date'] = $order->created_at?->toIso8601String();
            }
            if (!$ipGroups[$ip]['last_order_date'] || $order->created_at?->gt($ipGroups[$ip]['last_order_date'])) {
                $ipGroups[$ip]['last_order_date'] = $order->created_at?->toIso8601String();
            }

            // Lightweight order info for drill-down
            $ipGroups[$ip]['orders'][] = [
                'id'             => $order->id,
                'order_no'       => $order->order_no ?? ('#' . $order->id),
                'customer_name'  => $cName,
                'customer_phone' => $cPhone,
                'grand_total'    => (float) ($order->grand_total ?? 0),
                'status'         => $statusLabel,
                'created_at'     => $order->created_at?->toIso8601String(),
            ];
        }

        $resultList = [];
        $repeatIpCount = 0;

        $searchTerm = $request->filled('search') ? strtolower(trim($request->search)) : null;
        $minOrders = (int) ($request->min_orders ?? 1);

        foreach ($ipGroups as &$group) {
            $group['customers'] = array_values($group['customers']);
            $group['total_amount'] = round($group['total_amount'], 2);

            if ($group['total_orders'] > 1) {
                $repeatIpCount++;
            }

            if ($group['total_orders'] < $minOrders) {
                continue;
            }

            if ($searchTerm) {
                $matchesIp = str_contains(strtolower($group['ip']), $searchTerm);
                $matchesCustomer = collect($group['customers'])->contains(function ($c) use ($searchTerm) {
                    return str_contains(strtolower($c['name']), $searchTerm) || str_contains($c['phone'], $searchTerm);
                });
                $matchesOrderNo = collect($group['orders'])->contains(function ($o) use ($searchTerm) {
                    return str_contains(strtolower($o['order_no']), $searchTerm);
                });

                if (!$matchesIp && !$matchesCustomer && !$matchesOrderNo) {
                    continue;
                }
            }

            $resultList[] = $group;
        }

        usort($resultList, function ($a, $b) {
            if ($b['total_orders'] === $a['total_orders']) {
                return strcmp($b['last_order_date'] ?? '', $a['last_order_date'] ?? '');
            }
            return $b['total_orders'] <=> $a['total_orders'];
        });

        return ResponseHelper::success([
            'summary' => [
                'total_unique_ips' => count($ipGroups),
                'total_orders'     => $totalOrdersCount,
                'total_amount'     => round($totalSalesAmount, 2),
                'repeat_ip_count'  => $repeatIpCount,
            ],
            'data' => $resultList,
        ]);
    }
}

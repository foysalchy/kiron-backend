<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingRoiReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = Order::query()
            ->where(function ($q) {
                $q->whereNotNull('source_info')
                  ->orWhereNotNull('pixel_source_info');
            });

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->get();

        $sources = [];
        $campaigns = [];

        foreach ($orders as $order) {
            $sourceInfo = is_array($order->source_info) 
                ? $order->source_info 
                : (is_string($order->source_info) ? json_decode($order->source_info, true) : null);

            $pixelInfo = is_array($order->pixel_source_info) 
                ? $order->pixel_source_info 
                : (is_string($order->pixel_source_info) ? json_decode($order->pixel_source_info, true) : null);

            $info = array_merge($pixelInfo ?? [], $sourceInfo ?? []);

            // Source determination
            $utmSource = $info['utm_source'] ?? $info['source'] ?? null;

            if (empty($utmSource) || $utmSource === 'Direct/Organic') {
                if (!empty($info['fbc']) || (isset($info['url']) && str_contains($info['url'], 'fbclid='))) {
                    $utmSource = 'Facebook Ads';
                } elseif (!empty($info['fbp'])) {
                    $utmSource = 'Facebook (Meta Pixel)';
                } elseif (!empty($info['_ttp']) || (isset($info['url']) && str_contains($info['url'], 'ttclid='))) {
                    $utmSource = 'TikTok Ads';
                } else {
                    $utmSource = 'Direct/Organic';
                }
            }

            // Normalization
            $displaySource = match (strtolower(trim($utmSource))) {
                'facebook', 'fb', 'facebook ads' => 'Facebook Ads',
                'instagram', 'ig' => 'Instagram',
                'google', 'google ads', 'cpc' => 'Google Ads',
                'tiktok', 'tiktok ads' => 'TikTok Ads',
                'youtube' => 'YouTube',
                default => ucfirst($utmSource),
            };

            $orderAmount = (float) ($order->grand_total ?? $order->total_amount ?? 0);

            // 1. Source aggregation
            if (!isset($sources[$displaySource])) {
                $sources[$displaySource] = [
                    'source' => $displaySource,
                    'total_orders' => 0,
                    'total_revenue' => 0,
                ];
            }
            $sources[$displaySource]['total_orders']++;
            $sources[$displaySource]['total_revenue'] += $orderAmount;

            // 2. Campaign extraction
            $campaignName = trim((string)($info['utm_campaign'] ?? $info['campaign'] ?? $info['campaign_name'] ?? ''));
            if (empty($campaignName) && !empty($info['url'])) {
                if (preg_match('/[?&]utm_campaign=([^&#]+)/i', $info['url'], $matches)) {
                    $campaignName = trim(urldecode($matches[1]));
                }
            }
            if (empty($campaignName)) {
                $campaignName = 'No Campaign / Direct';
            }

            $statusValue = (int) ($order->status instanceof Status ? $order->status->value : $order->status);
            $statusLabel = $order->status_label ?? (Status::tryFrom($statusValue)?->label() ?? 'Unknown');

            $campaignKey = $campaignName . '___' . $displaySource;
            if (!isset($campaigns[$campaignKey])) {
                $campaigns[$campaignKey] = [
                    'campaign'       => $campaignName,
                    'source'         => $displaySource,
                    'total_orders'   => 0,
                    'total_revenue'  => 0,
                    'status_summary' => [
                        'pending'    => 0,
                        'confirmed'  => 0,
                        'processing' => 0,
                        'shipped'    => 0,
                        'delivered'  => 0,
                        'cancelled'  => 0,
                        'fake'       => 0,
                        'returned'   => 0,
                        'other'      => 0,
                    ],
                    'statuses'       => [],
                ];
            }

            $campaigns[$campaignKey]['total_orders']++;
            $campaigns[$campaignKey]['total_revenue'] += $orderAmount;

            // Categorize into summary bucket
            $bucket = match ($statusValue) {
                2, 13, 16, 36   => 'pending',
                3, 4            => 'confirmed',
                5, 27           => 'processing',
                6, 28, 29       => 'shipped',
                7, 8, 9         => 'delivered',
                10, 23          => 'cancelled',
                38              => 'fake',
                11, 30, 31, 32  => 'returned',
                default         => 'other',
            };
            $campaigns[$campaignKey]['status_summary'][$bucket]++;

            // Exact status itemization
            if (!isset($campaigns[$campaignKey]['statuses'][$statusValue])) {
                $campaigns[$campaignKey]['statuses'][$statusValue] = [
                    'id'      => $statusValue,
                    'name'    => $statusLabel,
                    'count'   => 0,
                    'revenue' => 0,
                ];
            }
            $campaigns[$campaignKey]['statuses'][$statusValue]['count']++;
            $campaigns[$campaignKey]['statuses'][$statusValue]['revenue'] += $orderAmount;
        }

        // Format statuses as indexed array sorted by count desc
        foreach ($campaigns as &$camp) {
            $camp['statuses'] = array_values($camp['statuses']);
            usort($camp['statuses'], fn($a, $b) => $b['count'] <=> $a['count']);
        }
        unset($camp);

        // Sort campaigns & sources by total_orders desc
        uasort($campaigns, fn($a, $b) => $b['total_orders'] <=> $a['total_orders']);
        uasort($sources, fn($a, $b) => $b['total_orders'] <=> $a['total_orders']);

        return ResponseHelper::success([
            'sources'   => array_values($sources),
            'campaigns' => array_values($campaigns),
        ]);
    }
}

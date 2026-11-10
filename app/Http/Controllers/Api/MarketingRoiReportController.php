<?php

namespace App\Http\Controllers\Api;

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

        $report = [];
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

            if (!isset($report[$displaySource])) {
                $report[$displaySource] = [
                    'source' => $displaySource,
                    'total_orders' => 0,
                    'total_revenue' => 0,
                ];
            }

            $report[$displaySource]['total_orders']++;
            $report[$displaySource]['total_revenue'] += (float) ($order->grand_total ?? $order->total_amount ?? 0);
        }

        return ResponseHelper::success(array_values($report));
    }
}

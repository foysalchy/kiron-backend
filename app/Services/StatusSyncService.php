<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StatusMapping;
use App\Models\WocommerceSetting;
use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\Log;

class StatusSyncService
{
    public function syncOrderStatus(Order $order): void
    {
        if (empty($order->source_info) || !isset($order->source_info['source_name'])) {
            return;
        }

        $sourceName = $order->source_info['source_name'];
        
        // Lookup mapping for this kiron_status and company
        $mappingRow = StatusMapping::where('company_id', $order->company_id)
            ->where('kiron_status', $order->status)
            ->first();

        if (!$mappingRow || empty($mappingRow->mappings[$sourceName])) {
            return;
        }

        $providerStatus = $mappingRow->mappings[$sourceName];

        try {
            if ($sourceName === 'woo' && isset($order->source_info['source_order_id'])) {
                $this->syncToWooCommerce($order, $providerStatus);
            }
            // Add pathao, redex, steadfast etc. here later
        } catch (\Exception $e) {
            Log::error("Failed to sync order status to $sourceName: " . $e->getMessage());
        }
    }

    protected function syncToWooCommerce(Order $order, string $wooStatus): void
    {
        $setting = WocommerceSetting::where('company_id', $order->company_id)->first();
        if (!$setting) {
            return;
        }

        $client = new Client(
            $setting->site_url,
            $setting->consumer_key,
            $setting->consumer_secret,
            [
                'version' => 'wc/v3',
                'verify_ssl' => false,
            ]
        );

        $client->put('orders/' . $order->source_info['source_order_id'], [
            'status' => $wooStatus
        ]);

        Log::info("Successfully synced Kiron order {$order->id} to WooCommerce status {$wooStatus}");
    }
}

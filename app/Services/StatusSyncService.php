<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StatusMapping;
use App\Jobs\SyncOrderStatusToProvider;
use Illuminate\Support\Facades\Log;

class StatusSyncService
{
    public function syncOrderStatus(Order $order): void
    {
        if (empty($order->source_info) || !isset($order->source_info['source_name'])) {
            return;
        }

        $sourceName = $order->source_info['source_name'];
        
        // Lookup mapping for this self_status and company
        $mappingRow = StatusMapping::where('company_id', $order->company_id)
            ->where('self_status', $order->status)
            ->first();

        if (!$mappingRow || empty($mappingRow->mappings[$sourceName])) {
            return;
        }
        $sourceOrderId = $order->source_info['source_order_id'] ?? null;

        if ($sourceOrderId) {
            SyncOrderStatusToProvider::dispatch(
                $order->id,
                $order->company_id,
                $sourceName,
                $sourceOrderId,
                $providerStatus
            );
        }
    }
}

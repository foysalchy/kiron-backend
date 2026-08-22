<?php

namespace App\Jobs;

use App\Models\WocommerceSetting;
use Automattic\WooCommerce\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncOrderStatusToProvider implements ShouldQueue
{
    use Queueable;

    public $orderId;
    public $companyId;
    public $sourceName;
    public $sourceOrderId;
    public $providerStatus;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $companyId, $sourceName, $sourceOrderId, $providerStatus)
    {
        $this->orderId = $orderId;
        $this->companyId = $companyId;
        $this->sourceName = $sourceName;
        $this->sourceOrderId = $sourceOrderId;
        $this->providerStatus = $providerStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->sourceName === 'woo') {
                $this->syncToWooCommerce();
            }
            // Add pathao, redex, steadfast etc. here later
        } catch (\Exception $e) {
            Log::error("Failed to sync order status to {$this->sourceName}: " . $e->getMessage());
        }
    }

    protected function syncToWooCommerce(): void
    {
        $setting = WocommerceSetting::where('company_id', $this->companyId)->first();
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

        $client->put('orders/' . $this->sourceOrderId, [
            'status' => $this->providerStatus
        ]);

        Log::info("Successfully synced Kiron order {$this->orderId} to WooCommerce status {$this->providerStatus}");
    }
}

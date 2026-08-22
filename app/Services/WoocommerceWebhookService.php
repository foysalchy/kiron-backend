<?php

namespace App\Services;

use App\Models\WocommerceSetting;
use App\Models\Product;
use App\Models\Party;
use App\Models\Warehouse;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class WoocommerceWebhookService
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function handleOrderWebhook(int $settingId, array $orderData, bool $force = false)
    {
        $setting = WocommerceSetting::findOrFail($settingId);
        
        // Skip if order sync is turned off and not forced
        if (!$setting->order_sync && !$force) {
            return;
        }

        // 1. Prepare Customer
        $customer = $this->findOrCreateCustomer($setting->company_id, $orderData['billing']);

        // 2. Prepare Items
        $items = $this->prepareItems($setting->company_id, $orderData['line_items']);
        if (empty($items)) {
            Log::info("Skipping WooCommerce order #{$orderData['id']} as no matching imported products were found in Kiron.");
            return; // User requested to only bring orders for products imported from woocommerce
        }

        // 3. Get Default Warehouse
        $warehouse = Warehouse::where('company_id', $setting->company_id)->first();
        if (!$warehouse) {
            throw ApiException::serverError('No warehouse found for company. Please create a warehouse first.');
        }

        // 4. Map to Kiron Order Format
        $orderPayload = [
            'company_id' => $setting->company_id,
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'type' => 'sales',
            'order_date' => date('Y-m-d H:i:s', strtotime($orderData['date_created'])),
            'is_walk_in' => 0,
            
            // Financials
            'other_charges' => $orderData['shipping_total'] ?? 0,
            'discount_on_all' => $orderData['discount_total'] ?? 0,
            'round_off' => 0,
            'note' => $orderData['customer_note'] ?? null,
            
            'items' => $items,
            
            // Payments
            'payments' => [],
            'status' => \App\Enums\Status::Pending->value,
            'is_due' => true,
        ];
        
        $orderPayload['source_info'] = [
            'source_name' => 'woo',
            'source_order_id' => $orderData['id'],
            'status' => $orderData['status']
        ];

        try {
            return $this->orderService->createOrder($orderPayload);
        } catch (\Exception $e) {
            Log::error('Failed to create order from WooCommerce Webhook: ' . $e->getMessage());
            throw $e;
        }
    }

    private function findOrCreateCustomer(int $companyId, array $billing)
    {
        $phone = $billing['phone'] ?? null;
        if (!$phone) {
            $phone = '0000000000'; // Fallback
        }

        $customer = Party::where('company_id', $companyId)
            ->where('type', 2) // 2 = Customer
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            $customer = Party::create([
                'company_id' => $companyId,
                'type' => 2,
                'name' => trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: 'WooCommerce Customer',
                'phone' => $phone,
                'email' => $billing['email'] ?? null,
                'address' => $billing['address_1'] ?? null,
                'status' => 1,
            ]);
        }

        return $customer;
    }

    private function prepareItems(int $companyId, array $lineItems)
    {
        $items = [];
        
        foreach ($lineItems as $line) {
            $wooProductId = $line['product_id'];
            $wooVariationId = $line['variation_id'];

            // Find matching product in Kiron by source_id
            $productQuery = Product::where('company_id', $companyId)
                ->where('source_info->source_id', $wooProductId);
                
            $product = $productQuery->first();

            if (!$product) {
                continue; // Skip items that aren't synced
            }
            
            $variationId = null;
            if ($wooVariationId && $wooVariationId > 0) {
                $variation = \App\Models\ProductVariation::where('product_id', $product->id)
                    ->where('sku', $line['sku'])
                    ->first();
                if ($variation) {
                    $variationId = $variation->id;
                }
            }

            $items[] = [
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'quantity' => $line['quantity'],
                'unit_price' => $line['price'],
                'discount' => 0,
                'tax' => $line['total_tax'] ?? 0,
            ];
        }

        return $items;
    }
}

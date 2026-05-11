<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;

class ProductWiseSalesReportService
{
    private array $deliveredStatuses = [Status::Delivered->value];

    private array $returnedStatuses = [
        Status::ReturntoCourier->value,
        Status::ReturnReceived->value,
        Status::ReturnRequest->value,
    ];
public function generate(array $filters): array
{
    $startDate       = $filters['start_date'];
    $endDate         = $filters['end_date'];
    $megaCategoryId  = $filters['mega_category_id'] ?? null;
    $brandId         = $filters['brand_id'] ?? null;
    $productId       = $filters['product_id'] ?? null;
    $statusFilter    = $filters['status_filter'] ?? 'delivered';
    $sortBy          = $filters['sort_by'] ?? 'quantity';

    // ── 1. Load Orders Once ──
    $statuses = $statusFilter === 'delivered'
        ? $this->deliveredStatuses
        : array_merge($this->deliveredStatuses, $this->returnedStatuses);

    $orders = Order::whereBetween('order_date', [$startDate, $endDate])
        ->whereIn('status', $statuses)
        ->get(['id', 'status']);

    $orderStatusMap = $orders->pluck('status', 'id');

    $orderIds = $orders->pluck('id');

    // ── 2. Get Order Details ──
    $orderDetailsQuery = OrderDetail::whereIn('order_id', $orderIds)
        ->with([
            'product.brand',
            'variation.attributes.attributeGroup',
            'variation.attributes.attributeValue'
        ]);

    // Product Filter
    if ($productId) {
        $orderDetailsQuery->where('product_id', $productId);
    }

    // Brand Filter
    if ($brandId) {
        $orderDetailsQuery->whereHas('product', function ($q) use ($brandId) {
            $q->where('brand_id', $brandId);
        });
    }

    // Mega Category Filter
    if ($megaCategoryId) {
        $megaCategoryId = (int) $megaCategoryId;

        $orderDetailsQuery->whereHas('product', function ($q) use ($megaCategoryId) {
            $q->whereJsonContains('mega_category_ids', $megaCategoryId);
        });
    }

    $orderDetails = $orderDetailsQuery->get();

    // ── 3. Group Product + Variation ──
    $grouped = [];

    foreach ($orderDetails as $detail) {

        $productIdValue   = $detail->product_id;
        $variationIdValue = $detail->variation_id;

        $key = $variationIdValue
            ? "p{$productIdValue}_v{$variationIdValue}"
            : "p{$productIdValue}";

        if (!isset($grouped[$key])) {

            $grouped[$key] = [
                'product_id'        => $productIdValue,
                'product_name'      => $detail->product->title ?? 'Unknown',
                'product_type'      => $detail->product->type ?? 'single',
                'brand'             => $detail->product->brand->name ?? 'No Brand',
                'category'          => $this->extractMegaCategory($detail->product),

                'variation_id'      => $variationIdValue,
                'variation_name'    => $variationIdValue
                    ? $this->formatVariationName($detail->variation)
                    : null,

                'sku'               => $variationIdValue
                    ? ($detail->variation->sku ?? null)
                    : ($detail->product->sku_code ?? null),

                'sold_qty'          => 0,
                'return_qty'        => 0,
                'net_qty'           => 0,

                'sales_amount'      => 0,
                'return_amount'     => 0,
                'net_sales'         => 0,

                'purchase_cost'     => 0,
                'profit'            => 0,
                'profit_margin'     => 0,

                'total_orders'      => 0,
            ];
        }

        $status = $orderStatusMap[$detail->order_id] ?? null;

        $isDelivered = in_array($status, $this->deliveredStatuses);
        $isReturned  = in_array($status, $this->returnedStatuses);

        // ── Delivered ──
        if ($isDelivered) {

            $grouped[$key]['sold_qty'] += $detail->quantity;

            $grouped[$key]['sales_amount'] += $detail->total;

            $grouped[$key]['total_orders'] += 1;
        }

        // ── Returned ──
        if ($isReturned) {

            $grouped[$key]['return_qty'] += $detail->quantity;

            $grouped[$key]['return_amount'] += $detail->total;
        }
    }

    // ── 4. Final Calculation ──
    foreach ($grouped as &$item) {

        $purchasePrice = $this->resolvePurchasePrice(
            $item['product_id'],
            $item['variation_id']
        );

        // Qty
        $item['net_qty'] = $item['sold_qty'] - $item['return_qty'];

        // Sales
        $item['net_sales'] = $item['sales_amount'] - $item['return_amount'];

        // Purchase
        $item['purchase_cost'] = $purchasePrice * $item['net_qty'];

        // Profit
        $item['profit'] = $item['net_sales'] - $item['purchase_cost'];

        // Margin
        $item['profit_margin'] = $item['net_sales'] > 0
            ? round(($item['profit'] / $item['net_sales']) * 100, 2)
            : 0;
    }

    // ── 5. Group Parent Product ──
    $products = [];

    foreach ($grouped as $item) {

        $productIdValue = $item['product_id'];

        if (!isset($products[$productIdValue])) {

            $products[$productIdValue] = [

                'product_id'    => $productIdValue,
                'product_name'  => $item['product_name'],
                'product_type'  => $item['product_type'],
                'brand'         => $item['brand'],
                'category'      => $item['category'],

                'sku'           => $item['product_type'] === 'single'
                    ? $item['sku']
                    : null,

                'variations'    => [],

                'sold_qty'      => 0,
                'return_qty'    => 0,
                'net_qty'       => 0,

                'sales_amount'  => 0,
                'return_amount' => 0,
                'net_sales'     => 0,

                'purchase_cost' => 0,
                'profit'        => 0,
                'profit_margin' => 0,

                'total_orders'  => 0,
            ];
        }

        // Add Variation
        if ($item['variation_id']) {
            $products[$productIdValue]['variations'][] = $item;
        }

        // Summary
        $products[$productIdValue]['sold_qty'] += $item['sold_qty'];

        $products[$productIdValue]['return_qty'] += $item['return_qty'];

        $products[$productIdValue]['net_qty'] += $item['net_qty'];

        $products[$productIdValue]['sales_amount'] += $item['sales_amount'];

        $products[$productIdValue]['return_amount'] += $item['return_amount'];

        $products[$productIdValue]['net_sales'] += $item['net_sales'];

        $products[$productIdValue]['purchase_cost'] += $item['purchase_cost'];

        $products[$productIdValue]['profit'] += $item['profit'];

        $products[$productIdValue]['total_orders'] += $item['total_orders'];
    }

    // ── 6. Parent Margin ──
    foreach ($products as &$product) {

        $product['profit_margin'] = $product['net_sales'] > 0
            ? round(($product['profit'] / $product['net_sales']) * 100, 2)
            : 0;
    }

    // ── 7. Sorting ──
    $products = collect($products)
        ->sortByDesc(function ($item) use ($sortBy) {

            return match ($sortBy) {

                'sales'  => $item['net_sales'],
                'profit' => $item['profit'],

                default  => $item['sold_qty'],
            };
        })
        ->values()
        ->toArray();

    // ── 8. Summary ──
    $summary = [

        'total_products' => count($products),

        'total_sold_qty' => array_sum(array_column($products, 'sold_qty')),

        'total_return_qty' => array_sum(array_column($products, 'return_qty')),

        'total_net_qty' => array_sum(array_column($products, 'net_qty')),

        'total_sales' => round(array_sum(array_column($products, 'sales_amount')), 2),

        'total_return_amount' => round(array_sum(array_column($products, 'return_amount')), 2),

        'total_net_sales' => round(array_sum(array_column($products, 'net_sales')), 2),

        'total_purchase' => round(array_sum(array_column($products, 'purchase_cost')), 2),

        'total_profit' => round(array_sum(array_column($products, 'profit')), 2),
    ];

    $summary['overall_profit_margin'] = $summary['total_net_sales'] > 0
        ? round(($summary['total_profit'] / $summary['total_net_sales']) * 100, 2)
        : 0;

    // ── 9. Response ──
    return [

        'date_range' => [
            'start' => $startDate,
            'end'   => $endDate
        ],

        'filters' => [
            'mega_category_id' => $megaCategoryId,
            'brand_id'         => $brandId,
            'product_id'       => $productId,
            'status_filter'    => $statusFilter,
            'sort_by'          => $sortBy,
        ],

        'summary' => $summary,

        'products' => $products,
    ];
}

    // ── Purchase Price Resolve ──
    private function resolvePurchasePrice(int $productId, ?int $variationId): float
    {
        if ($variationId) {
            // 1. Last purchase
            $lastPurchase = PurchaseDetail::where('variation_id', $variationId)
                ->orderByDesc('created_at')
                ->value('purchase_price');

            if ($lastPurchase) return (float) $lastPurchase;

            // 2. Variation table
            return (float) (ProductVariation::find($variationId)?->purchase_price ?? 0);
        }

        // Single product
        $lastPurchase = PurchaseDetail::where('product_id', $productId)
            ->whereNull('variation_id')
            ->orderByDesc('created_at')
            ->value('purchase_price');

        if ($lastPurchase) return (float) $lastPurchase;

        return (float) (Product::find($productId)?->purchase_price ?? 0);
    }

    // ── Extract Mega Category ──
    private function extractMegaCategory(Product $product): ?string
    {
        $megaIds = is_string($product->mega_category_ids)
            ? json_decode($product->mega_category_ids, true)
            : $product->mega_category_ids;

        if (empty($megaIds)) return 'Uncategorized';

        $firstId = is_array($megaIds) ? $megaIds[0] : $megaIds;

        return \App\Models\MegaCategory::find($firstId)?->name ?? 'Unknown';
    }

    // ── Format Variation Name ──
    private function formatVariationName(?ProductVariation $variation): ?string
    {
        if (!$variation || !$variation->attributes) return null;

        return $variation->attributes
            ->filter(fn($attr) => $attr->attributeGroup && $attr->attributeValue) // ✅ null check
            ->map(fn($attr) => "{$attr->attributeGroup->name}: {$attr->attributeValue->name}")
            ->join(', ') ?: null;
    }
}

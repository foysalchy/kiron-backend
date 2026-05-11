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

        // ── 1. Filter Orders ──
        $statuses = $statusFilter === 'delivered'
            ? $this->deliveredStatuses
            : array_merge($this->deliveredStatuses, $this->returnedStatuses);

        $orderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $statuses)
            ->pluck('id');

        // ── 2. Get Order Details ──
        $orderDetailsQuery = OrderDetail::whereIn('order_id', $orderIds)
            ->with(['product.brand', 'variation.attributes.attributeGroup', 'variation.attributes.attributeValue']);

        // Filter by product/brand/category
        if ($productId) {
            $orderDetailsQuery->where('product_id', $productId);
        }

        if ($brandId) {
            $orderDetailsQuery->whereHas('product', fn($q) => $q->where('brand_id', $brandId));
        }

        if ($megaCategoryId) {
            $megaCategoryId = (int) $megaCategoryId; 

            $orderDetailsQuery->whereHas('product', function ($q) use ($megaCategoryId) {
                $q->whereJsonContains('mega_category_ids', $megaCategoryId);
            });
        }
        $orderDetails = $orderDetailsQuery->get();

        // ── 3. Separate Delivered & Returned ──
        $deliveredOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->deliveredStatuses)
            ->pluck('id');

        $returnedOrderIds = Order::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', $this->returnedStatuses)
            ->pluck('id');

        // ── 4. Group by Product & Variation ──
        $grouped = [];

        foreach ($orderDetails as $detail) {
            $productId   = $detail->product_id;
            $variationId = $detail->variation_id;
            $key         = $variationId ? "p{$productId}_v{$variationId}" : "p{$productId}";

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_id'        => $productId,
                    'product_name'      => $detail->product->title ?? 'Unknown',
                    'product_type'      => $detail->product->type ?? 'single',
                    'brand'             => $detail->product->brand->name ?? 'No Brand',
                    'category'          => $this->extractMegaCategory($detail->product),
                    'variation_id'      => $variationId,
                    'variation_name'    => $variationId ? $this->formatVariationName($detail->variation) : null,
                    'sku'               => $variationId ? ($detail->variation->sku ?? null) : ($detail->product->sku_code ?? null),
                    'sold_qty'          => 0,
                    'return_qty'        => 0,
                    'net_qty'           => 0,
                    'sales_amount'      => 0,
                    'purchase_cost'     => 0,
                    'profit'            => 0,
                    'profit_margin'     => 0,
                    'total_orders'      => 0,
                ];
            }

            $isDelivered = $deliveredOrderIds->contains($detail->order_id);
            $isReturned  = $returnedOrderIds->contains($detail->order_id);

            if ($isDelivered) {
                $grouped[$key]['sold_qty']      += $detail->quantity;
                $grouped[$key]['sales_amount']  += $detail->total;
                $grouped[$key]['total_orders']  += 1;
            }

            if ($isReturned) {
                $grouped[$key]['return_qty'] += $detail->quantity;
            }
        }

        // ── 5. Calculate Purchase Cost & Profit ──
        foreach ($grouped as $key => &$item) {
            $purchasePrice = $this->resolvePurchasePrice($item['product_id'], $item['variation_id']);

            $item['net_qty']        = $item['sold_qty'] - $item['return_qty'];
            $item['purchase_cost']  = $purchasePrice * $item['sold_qty'];
            $item['profit']         = $item['sales_amount'] - $item['purchase_cost'];
            $item['profit_margin']  = $item['sales_amount'] > 0
                ? round(($item['profit'] / $item['sales_amount']) * 100, 2)
                : 0;
        }

        // ── 6. Group by Product (with variations as children) ──
        $products = [];

        foreach ($grouped as $item) {
            $productId = $item['product_id'];

            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'product_id'    => $productId,
                    'product_name'  => $item['product_name'],
                    'product_type'  => $item['product_type'],
                    'brand'         => $item['brand'],
                    'category'      => $item['category'],
                    'sku'           => $item['product_type'] === 'single' ? $item['sku'] : null,
                    'variations'    => [],
                    'sold_qty'      => 0,
                    'return_qty'    => 0,
                    'net_qty'       => 0,
                    'sales_amount'  => 0,
                    'purchase_cost' => 0,
                    'profit'        => 0,
                    'profit_margin' => 0,
                    'total_orders'  => 0,
                ];
            }

            if ($item['variation_id']) {
                $products[$productId]['variations'][] = $item;
            }

            $products[$productId]['sold_qty']      += $item['sold_qty'];
            $products[$productId]['return_qty']    += $item['return_qty'];
            $products[$productId]['net_qty']       += $item['net_qty'];
            $products[$productId]['sales_amount']  += $item['sales_amount'];
            $products[$productId]['purchase_cost'] += $item['purchase_cost'];
            $products[$productId]['profit']        += $item['profit'];
            $products[$productId]['total_orders']  += $item['total_orders'];
        }

        // Recalculate profit margin for parent
        foreach ($products as &$product) {
            $product['profit_margin'] = $product['sales_amount'] > 0
                ? round(($product['profit'] / $product['sales_amount']) * 100, 2)
                : 0;
        }

        // ── 7. Sort ──
        $products = collect($products)->sortByDesc(function ($item) use ($sortBy) {
            return match ($sortBy) {
                'sales'    => $item['sales_amount'],
                'profit'   => $item['profit'],
                default    => $item['sold_qty'],
            };
        })->values()->toArray();

        // ── 8. Summary ──
        $summary = [
            'total_products'    => count($products),
            'total_sold_qty'    => array_sum(array_column($products, 'sold_qty')),
            'total_return_qty'  => array_sum(array_column($products, 'return_qty')),
            'total_net_qty'     => array_sum(array_column($products, 'net_qty')),
            'total_sales'       => round(array_sum(array_column($products, 'sales_amount')), 2),
            'total_purchase'    => round(array_sum(array_column($products, 'purchase_cost')), 2),
            'total_profit'      => round(array_sum(array_column($products, 'profit')), 2),
        ];

        $summary['overall_profit_margin'] = $summary['total_sales'] > 0
            ? round(($summary['total_profit'] / $summary['total_sales']) * 100, 2)
            : 0;

        return [
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'filters'    => [
                'mega_category_id' => $megaCategoryId,
                'brand_id'         => $brandId,
                'product_id'       => $productId,
                'status_filter'    => $statusFilter,
                'sort_by'          => $sortBy,
            ],
            'summary'    => $summary,
            'products'   => $products,
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

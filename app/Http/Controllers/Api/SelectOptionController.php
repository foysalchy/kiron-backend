<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\MegaCategory;
use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SelectOptionController extends Controller
{
    /**
     * Get select options for various entities
     */
    public function warehouseOptions()
    {
        return Warehouse::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function supplierOptions()
    {
        return Party::where('type', 1)->orderBy('name', 'asc')->get();
    }
    public function customersOptions()
    {
        return Party::where('type', 2)->orderBy('name', 'asc')->get();
    }
    public function productOptions()
    {
        return Product::with([
            'brand',
            'galleries',

            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.stocks.warehouse',
        ])->orderBy('title', 'asc')->get();
    }
    public function getProductByWarehouse($warehouseId)
    {
        $products = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            // Variation এর জন্য শুধুমাত্র স্পেসিফিক ওয়্যারহাউসের স্টক লোড করছি
            'variations.stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            },
            'variations.stocks.warehouse',
        ])
            ->where(function ($q) use ($warehouseId) {
                // Single Product-এর জন্য
                $q->whereJsonContains('warehouse_info', [
                    'warehouse_id' => (string) $warehouseId
                ])
                    // Variation Product-এর জন্য
                    ->orWhereHas('variations.stocks', function ($stockQuery) use ($warehouseId) {
                        $stockQuery->where('warehouse_id', $warehouseId)
                            ->where('quantity', '>', 0);
                    });
            })
            ->orderBy('title', 'asc')
            ->get();

        // ডাটাবেস থেকে তুলে আনার পর Collection Map করে শুধুমাত্র স্পেসিফিক স্টক সেট করা হচ্ছে
        $mappedProducts = $products->map(function ($product) use ($warehouseId) {
            $warehouseTotalStock = 0;

            if ($product->type === 'single' && is_array($product->warehouse_info)) {
                // JSON থেকে শুধুমাত্র রিকোয়েস্ট করা ওয়্যারহাউসের ডাটা ফিল্টার করা
                $filteredWarehouseInfo = collect($product->warehouse_info)
                    ->where('warehouse_id', (string) $warehouseId)
                    ->values(); // Reset array index

                // ওই ওয়্যারহাউসের টোটাল কোয়ান্টিটি (যদি একাধিক বিন থাকে)
                $warehouseTotalStock = $filteredWarehouseInfo->sum('quantity');

                // রেসপন্স ক্লিন করার জন্য অন্য ওয়্যারহাউসের ডাটা মুছে শুধু স্পেসিফিক ডাটা সেট করা
                $product->warehouse_info = $filteredWarehouseInfo->toArray();
            }

            if ($product->type === 'variation') {
                $product->variations->map(function ($variation) {
                    // eager loaded relations থেকে স্টক যোগ করা (যেহেতু কুয়েরিতে ফিল্টার করা আছে)
                    $variationStock = $variation->stocks->sum('quantity');

                    // Variation-এর স্টক স্পেসিফিক ওয়্যারহাউসের স্টকে ওভাররাইড করা
                    $variation->available_stock = $variationStock;
                    $variation->stock_quantity = $variationStock;

                    return $variation;
                });

                // প্যারেন্ট প্রোডাক্টের টোটাল স্টক ক্যালকুলেট করা
                $warehouseTotalStock = $product->variations->sum('available_stock');
            }

            // মূল প্রোডাক্টের available_stock ওভাররাইড করে দেওয়া
            $product->available_stock = $warehouseTotalStock;
            $product->stock_quantity = $warehouseTotalStock;

            return $product;
        });

        return $mappedProducts->filter(function ($product) {
            return $product->available_stock > 0;
        })->values(); 
    }
    public function purchaseOptions()
    {
        return Purchase::orderBy('purchase_date', 'desc')->get();
    }

    public function attributeGroupOptions()
    {
        return AttributeGroup::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function megaCategoryOptions()
    {
        return MegaCategory::select('id', 'name')->orderBy('name', 'asc')->get();
    }
}

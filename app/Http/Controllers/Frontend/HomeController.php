<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class HomeController extends FrontendController
{
    public function index()
    {

        $categories = MegaCategory::with('subCategories.miniCategories')->get();

        $newArrivals = Product::with(['brand', 'variations.attributes.attributeValue'])->active()->latest()->take(10)->get();
        // \Log::info($newArrivals);
        //for product groups
        $productGroups = ProductGroup::where('status', Status::Active->value)
            ->where('is_frontend', 1)
            ->get()
            ->map(function ($group) {
                $group->products = Product::whereIn('id', $group->product_ids ?? [])
                    ->active()
                    ->take(15)
                    ->get();
                return $group;
            })
            ->filter(fn($group) => $group->products->count() > 0);

        $brands = Brand::active()->latest()->take(10)->get();

        $popularProducts = Product::with(['brand', 'variations'])
            ->active()
            ->withSum('orderDetails as total_sales', 'quantity')
            ->orderByDesc('total_sales')
            ->take(12)
            ->get();

        return  $this->view('frontend.home', compact('categories', 'newArrivals', 'brands', 'popularProducts', 'productGroups'));
    }
}

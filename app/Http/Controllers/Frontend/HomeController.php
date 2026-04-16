<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $company  = getCurrentCompany();
        $template = $company->template_name;

        $categories = MegaCategory::with('subCategories.miniCategories')
            ->where('company_id', $company->company_id ?? $company->id)
            ->get();

        $newArrivals = Product::with(['brand', 'variations.attributes.attributeValue'])
            ->where('company_id', $company->company_id ?? $company->id)
            ->active()
            ->latest()
            ->take(10)
            ->get();
        // \Log::info($newArrivals);
        //for product groups
        $companyId = $company->company_id ?? $company->id;

        $productGroups = ProductGroup::where('company_id', $companyId)
            ->where('status', Status::Active->value)
            ->where('is_frontend', 1)
            ->get()
            ->map(function ($group) use ($companyId) {
                $group->products = Product::whereIn('id', $group->product_ids ?? [])
                    ->where('company_id', $companyId)
                    ->active()
                    ->take(15)
                    ->get();
                return $group;
            })
            ->filter(function ($group) {
                return $group->products->count() > 0;
            });

        $brands = Brand::active()->latest()->take(10)->get();

        $popularProducts = Product::with(['brand', 'variations'])
            ->active()
            ->withSum('orderDetails as total_sales', 'quantity')
            ->orderByDesc('total_sales')
            ->take(12)
            ->get();

        return view($template . '.frontend.home', compact('categories', 'newArrivals', 'brands', 'popularProducts','productGroups'));
    }
    public function about()
    {
        $setup    = getCurrentCompany();
        $template = $setup->template_name;

        return view($template . '.frontend.about', compact('setup'));
    }
}

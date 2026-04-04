<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $setup    = getCurrentCompany();
        $template = $setup->template_name;

        $categories = MegaCategory::with('subCategories')
        ->where('company_id', $setup->id)
        ->get();

        $newArrivals = Product::with(['brand'])
        ->active()
        ->latest()
        ->take(10)
        ->get();
        $brands = Brand::active()->latest()->take(10)->get();

        $popularProducts = Product::with(['brand','variations'])
        ->active()
        ->withSum('orderDetails as total_sales', 'quantity')
        ->orderByDesc('total_sales')
        ->take(12)
        ->get();

        return view($template . '.frontend.home', compact('setup','categories','newArrivals','brands','popularProducts'));
    }
    public function about()
    {
        $setup    = getCurrentCompany();
        $template = $setup->template_name;

        return view($template . '.frontend.about', compact('setup'));
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $brands = Brand::where('company_id', $company->id)
            ->active()
            ->withCount('products')
            ->get();
        return view($template . '.frontend.brand',compact('brands'));
    }
    
}

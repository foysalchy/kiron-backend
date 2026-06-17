<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends FrontendController
{
    public function index()
    {
        $brands = Brand::active()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->withCount('products')
            ->get();

        return $this->view('frontend.brand', compact('brands'));
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends FrontendController
{
    public function index()
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        $brands = Cache::remember("brand_list_page_{$companyId}", $ttl, function () use ($companyId) {
            return  Brand::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->withCount('products')
                ->get();
        });
        return $this->view('frontend.brand', compact('brands'));
    }
}

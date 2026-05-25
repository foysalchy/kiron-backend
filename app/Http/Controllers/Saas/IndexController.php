<?php

namespace App\Http\Controllers\Saas;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\MasterBrand;
use App\Models\MasterFeature;
use App\Models\Slider;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function home()
    {
        $slider = Slider::withoutCompanyScope()
            ->where('status', Status::Active->value)
            ->where('placement', 'hero')
            ->whereNull('company_id')
            ->latest()
            ->first();
        $brands = MasterBrand::where('status', Status::Active->value)
            ->latest()
            ->get();

        $features = MasterFeature::where('status', Status::Active->value)
            ->orderBy('placement')
            ->latest()
            ->get();

        return view('saas.frontend.index', compact('slider', 'brands', 'features'));
    }
}

<?php

namespace App\Http\Controllers\Saas;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CustomerReview;
use App\Models\MasterBrand;
use App\Models\MasterDemo;
use App\Models\MasterFeature;
use App\Models\Slider;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function home()
    {
        $sliders = Slider::withoutCompanyScope()
        ->where('status', Status::Active->value)
        ->where('placement', 'hero')
        ->whereNull('company_id')
        ->latest()
        ->get();
        $brands = MasterBrand::where('status', Status::Active->value)
            ->latest()
            ->get();

        $features = MasterFeature::where('status', Status::Active->value)
            ->orderBy('placement')
            ->latest()
            ->get();

        $reviewsQuery = CustomerReview::where('status', Status::Active->value);

        $avgRating = $reviewsQuery->avg('rating') ?: 0;
        $totalReviews = $reviewsQuery->count();

        $allReviews = CustomerReview::where('status', Status::Active->value)->latest()->get();

        $demos = MasterDemo::where('status', Status::Active->value)
            ->latest()
            ->get();

        $blogs = Blog::withoutCompanyScope()
            ->with('company')
            ->where('status', Status::Active->value)
            ->latest()
            ->take(3)
            ->get();
        return view('saas.frontend.index', compact(
            'sliders',
            'brands',
            'features',
            'avgRating',
            'totalReviews',
            'demos',
            'allReviews',
            'blogs'
        ));
    }
}

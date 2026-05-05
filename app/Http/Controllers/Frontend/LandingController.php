<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;

class LandingController extends FrontendController
{
    public function index($slug)
    {
        $landing = LandingPage::with('product')->where('slug', $slug)->firstOrFail();

    
        $product = $landing->product;

        return view('landing.landing1', compact('landing', 'product'));
    }
}

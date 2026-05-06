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

        // For now, we'll just return a view with the slug
        return view('landing.landing1', compact('slug'));
    }
}

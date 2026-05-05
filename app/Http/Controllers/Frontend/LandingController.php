<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index($slug)
    {
        // You can use the $slug to fetch relevant data for the landing page
        // For example, you might want to fetch a specific product or category based on the slug

        // For now, we'll just return a view with the slug
        return view('landing.landing1', compact('slug'));
    }
}

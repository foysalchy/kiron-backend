<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class AboutController extends FrontendController
{
    public function showPage($store,$slug)
    {
        $page = Page::where('slug', $slug)
                    ->where('status', 1)
                    ->firstOrFail();
        return $this->view('frontend.page', compact('page'));
    }
}

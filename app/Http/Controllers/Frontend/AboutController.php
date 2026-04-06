<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        return view($template . '.frontend.about');
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;
        return view($template . '.frontend.terms');
    }
    public function privacy()
    {
        $company = getCurrentCompany();
        $template = $company->template_name;
        return view($template . '.frontend.privacy');
    }
}

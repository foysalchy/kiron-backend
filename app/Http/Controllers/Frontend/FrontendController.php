<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    protected $company;
    protected $template;

    public function __construct()
    {
        $this->company  = getCurrentCompany();
        $this->template = $this->company->template_name;
    }

    protected function view(string $view, array $data = [])
    {
        $company = getCurrentCompany();
        $templateName = $company->template_name ?? '';

        return view($templateName . '.' . $view, $data);
    }
}

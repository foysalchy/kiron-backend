<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    protected $company;
    protected $template;
    protected $company_id;

    public function __construct()
    {
        $this->company  = getCurrentCompany();
        $this->template = $this->company->template_name;
        $this->company_id = $this->company->company_id ?? $this->company->id;
    }

    protected function view(string $view, array $data = [])
    {
        $company = getCurrentCompany();
        $templateName = $company->template_name ?? '';

        return view($templateName . '.' . $view, $data);
    }
}

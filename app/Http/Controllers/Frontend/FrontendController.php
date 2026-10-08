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
        
        if (request()->has('preview_theme')) {
            $this->template = 'template' . request('preview_theme');
        } else {
            $this->template = $this->company ? $this->company->template_name : 'template1';
        }

        $this->company_id = $this->company ? $this->company->company_id : null;
    }

    protected function view(string $view, array $data = [])
    {
        $company = getCurrentCompany();
        
        if (request()->has('preview_theme')) {
            $templateName = 'template' . request('preview_theme');
        } else {
            $templateName = $company->template_name ?? '';
        }

        return view($templateName . '.' . $view, $data);
    }

    public function previewCard()
    {
        $dummyProduct = \App\Models\Product::where('status',1)->first();
        if (!$dummyProduct) {
            return response('No products available for preview', 200);
        }
        
        // Render a minimal view with Tailwind injected (if not already included in layout)
        // Since we want exactly the card styles, we can wrap it in a div that loads app.css
        return view('preview-card', ['product' => $dummyProduct, 'template' => $this->template]);
    }
}

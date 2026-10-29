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
        
        $previewTheme = request('preview_theme');

        if ($previewTheme) {
            $this->template = 'template' . $previewTheme;
        } else {
            $this->template = $this->company ? $this->company->template_name : 'template1';
        }

        $this->company_id = $this->company ? $this->company->company_id : null;
    }

    protected function view(string $view, array $data = [])
    {
        $company = getCurrentCompany();
        $templateName = $this->template ?: ($company->template_name ?? 'template1');

        $previewTheme = request('preview_theme');
        \Illuminate\Support\Facades\View::share('activePreviewTheme', $previewTheme);

        if ($previewTheme) {
            $sharedFooterFeatures = view()->shared('footerFeatures');
            if (empty($sharedFooterFeatures) || (is_object($sharedFooterFeatures) && method_exists($sharedFooterFeatures, 'isEmpty') && $sharedFooterFeatures->isEmpty())) {
                \Illuminate\Support\Facades\View::share('footerFeatures', \App\Services\StorePreviewService::getDummyFooterFeatures());
            }

            $sharedFooterPages = view()->shared('footerPages');
            if (empty($sharedFooterPages) || (is_object($sharedFooterPages) && method_exists($sharedFooterPages, 'isEmpty') && $sharedFooterPages->isEmpty())) {
                \Illuminate\Support\Facades\View::share('footerPages', \App\Services\StorePreviewService::getDummyFooterPages());
            }

            $sharedSocialLinks = view()->shared('socialLinks');
            if (empty($sharedSocialLinks) || (is_object($sharedSocialLinks) && method_exists($sharedSocialLinks, 'isEmpty') && $sharedSocialLinks->isEmpty())) {
                \Illuminate\Support\Facades\View::share('socialLinks', \App\Services\StorePreviewService::getDummySocialLinks());
            }

            $sharedFooterBottomRight = view()->shared('footerBottomRight');
            if (empty($sharedFooterBottomRight) || (is_object($sharedFooterBottomRight) && method_exists($sharedFooterBottomRight, 'isEmpty') && $sharedFooterBottomRight->isEmpty())) {
                \Illuminate\Support\Facades\View::share('footerBottomRight', \App\Services\StorePreviewService::getDummyFooterBottomRight());
            }
        }

        return view($templateName . '.' . $view, $data);
    }

    public function previewCard()
    {
        $dummyProduct = \App\Models\Product::where('status',1)->first();
        if (!$dummyProduct) {
            return response('No products available for preview', 200);
        }

        $company = getCurrentCompany();
        $themeTemplate = $company->theme_template ?? [];

        $themeColor = new \stdClass();
        $themeColor->theme_template = [
            'primary_color'        => request('primary_color') ?: ($themeTemplate['primary_color'] ?? '#13565e'),
            'primary_text_color'   => request('primary_text_color') ?: ($themeTemplate['primary_text_color'] ?? '#ffffff'),
            'primary_hover_color'  => request('primary_hover_color') ?: ($themeTemplate['primary_hover_color'] ?? '#0d3d43'),
            'primary_hover_text'   => request('primary_hover_text') ?: ($themeTemplate['primary_hover_text'] ?? '#ffffff'),
            'secondary_color'      => request('secondary_color') ?: ($themeTemplate['secondary_color'] ?? '#f3f4f6'),
            'secondary_text_color' => request('secondary_text_color') ?: ($themeTemplate['secondary_text_color'] ?? '#1f2937'),
            'header_color'         => request('header_color') ?: ($themeTemplate['header_color'] ?? '#ffffff'),
            'header_text_color'    => request('header_text_color') ?: ($themeTemplate['header_text_color'] ?? '#1f2937'),
            'footer_color'         => request('footer_color') ?: ($themeTemplate['footer_color'] ?? '#1f2937'),
            'footer_text_color'    => request('footer_text_color') ?: ($themeTemplate['footer_text_color'] ?? '#ffffff'),
        ];
        \Illuminate\Support\Facades\View::share('themeColor', $themeColor);

        $storeSettings = new \stdClass();
        $storeSettings->is_review = (bool)($company->is_review ?? false);
        \Illuminate\Support\Facades\View::share('storeSettings', $storeSettings);

        $setup = \App\Models\SiteSetting::where('company_id', $this->company_id)->first() ?? new \App\Models\SiteSetting();
        \Illuminate\Support\Facades\View::share('setup', $setup);

        return view('preview-card', [
            'product'  => $dummyProduct,
            'template' => $this->template,
            'company'  => $company,
            'setup'    => $setup,
        ]);
    }
}

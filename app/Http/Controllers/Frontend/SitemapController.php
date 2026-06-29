<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\LandingPage;
use App\Models\MegaCategory;
use App\Models\Page;
use App\Models\Product;
use App\Enums\Status;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function robots()
    {
        $currentStore = getCurrentCompany();

        if (!$currentStore) {
            abort(404);
        }

        $companyId = $currentStore->company_id;
        $robotsContent = Cache::remember(
            "robots_{$companyId}",
            now()->addHours(6),
            function () use ($companyId) {
                // Customize the robots.txt content based on your requirements
                return "User-agent: *\nDisallow: /admin/\nSitemap: " . route('sitemap.index');
            }
        );

        return response($robotsContent, 200)
            ->header('Content-Type', 'text/plain');
    }
    public function index()
    {
        $currentStore = getCurrentCompany();

        if (!$currentStore) {
            abort(404);
        }

        $companyId = $currentStore->company_id;
        $data = Cache::remember(
    "sitemap_{$companyId}",
    now()->addHours(6),
    function () use ($companyId) {

        return [
            'products' => Product::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get(),

            'categories' => MegaCategory::where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get(),

            'brands' => Brand::where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get(),

            'blogs' => Blog::where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get(),

            'pages' => Page::where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get(),

            'landingPages' => class_exists(LandingPage::class)
                ? LandingPage::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->select('slug', 'updated_at')
                    ->get()
                : collect(),
        ];
    }
);

return response()
    ->view('sitemap.index', $data)
    ->header('Content-Type', 'application/xml');
            
    }
    
}
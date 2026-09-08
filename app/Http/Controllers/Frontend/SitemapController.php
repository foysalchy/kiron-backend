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
use App\Models\SiteSetting;
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

        // Fetch site setting to check if indexing is allowed
        $setup = SiteSetting::where('company_id', $companyId)->first();
        $allowIndex = $setup ? (bool)($setup->allow_search_engine_index ?? false) : false;
        
        $robotsContent = Cache::remember(
            "robots_{$companyId}_" . ($allowIndex ? 'index' : 'noindex'),
            now()->addHours(6),
            function () use ($allowIndex) {
                if (!$allowIndex) {
                    return <<<ROBOTS
User-agent: *
Disallow: /
Noindex: /
Nofollow: /
ROBOTS;
                }

                $url = url('/');
                return <<<ROBOTS
User-agent: *
Allow: /
Sitemap: {$url}/sitemap.xml
ROBOTS;
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

    public function llms()
    {
        $currentStore = getCurrentCompany();
        if (!$currentStore) abort(404);

        $companyId = $currentStore->company_id;

        $llmsContent = Cache::remember("llms_txt_{$companyId}", now()->addHours(6), function () use ($currentStore) {
            $metaTitle = $currentStore->title ?? $currentStore->shop_name ?? 'Our Shop';
            $metaDescription = $currentStore->description ?? 'Discover our premium products.';
            $shopName = $currentStore->shop_name ?? 'Our Shop';
            $url = url('/');

            $content = "Title: {$metaTitle}\n";
            $content .= "Description: {$metaDescription}\n";
            $content .= "--- \n\n"; // একটি সেপারেটর লাইন

            $content .= "# LLMS.txt for {$shopName}\n\n";
            $content .= "This file provides guidance for Large Language Models (LLMs) and AI crawlers.\n\n";

            $content .= "## Site Information\n";
            $content .= "- **Base URL:** {$url}\n";
            $content .= "- **Sitemap:** {$url}/sitemap.xml\n";
            $content .= "- **Meta Title:** {$metaTitle}\n";
            $content .= "- **Meta Description:** {$metaDescription}\n\n";

            $content .= "## Key Directories\n";
            $content .= "- **Products:** {$url}/shop\n";
            $content .= "- **Blogs:** {$url}/blog\n";
            $content .= "- **Brands:** {$url}/brands\n\n";

            $content .= "## Guidelines\n";
            $content .= "- Crawlers must respect the instructions in {$url}/robots.txt.\n";
            $content .= "- Avoid indexing private user dashboards or checkout pages.\n";
            $content .= "- Attribution is required for content used in training sets.";

            return $content;
        });

        return response($llmsContent, 200)->header('Content-Type', 'text/plain');
    }
    public function googleXml()
    {
        $currentStore = getCurrentCompany();
        if (!$currentStore) abort(404);

        $companyId = $currentStore->company_id;

        $xmlContent = Cache::remember("google_xml_{$companyId}", now()->addHours(6), function () use ($companyId, $currentStore) {

            $setup = SiteSetting::where('company_id', $companyId)->first();

            $products = Product::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->with(['brand', 'variations.attributes.attributeValue'])
                ->get();

            Product::loadCategoriesForCollection($products);

            return view('sitemap.google-xml', [
                'products' => $products,
                'setup'    => $setup
            ])->render();
        });

        return response($xmlContent, 200)->header('Content-Type', 'application/xml');
    }
    public function facebookCatalogCsv()
    {
        $currentStore = getCurrentCompany();
        if (!$currentStore) abort(404);

        $companyId = $currentStore->company_id;
        $setup = SiteSetting::where('company_id', $companyId)->first();
        $currency = $setup->currency ?? 'BDT';

        $products = Product::where('company_id', $companyId)
            ->where('status', Status::Active->value)
            ->with(['brand', 'variations.attributes.attributeValue', 'galleries', 'variations.galleries'])
            ->get();

        Product::loadCategoriesForCollection($products);

        $fileName = 'facebook_catalog_' . $companyId . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['id', 'title', 'description', 'availability', 'condition', 'price', 'sale_price', 'link', 'image_link', 'additional_image_link', 'brand', 'item_group_id', 'gtin', 'mpn', 'google_product_category'];

        $callback = function () use ($products, $columns, $setup, $currency) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                if ($product->type === 'single') {
                    // ─── Single Product Row ───
                    fputcsv($file, [
                        'PRD-' . $product->id,
                        $product->title,
                        strip_tags($product->short_description ?: $product->title),
                        $product->available_stock > 0 ? 'in stock' : 'out of stock',
                        'new',
                        number_format($product->regular_price, 2, '.', '') . ' ' . $currency,
                        $product->discount > 0 ? number_format($product->sale_price, 2, '.', '') . ' ' . $currency : '',
                        url($product->slug),
                        $product->thumbnail_url,
                        $product->galleries->pluck('image_url')->implode(','),
                        $product->brand->name ?? $setup->shop_name,
                        'PRD-' . $product->id,
                        '', // gtin
                        'PRD-' . $product->id, // mpn
                        $product->mega_categories->first()->name ?? 'General'
                    ]);
                } else {
                    // ─── Variable Product Rows
                    foreach ($product->variations as $variant) {
                        $vGallery = $variant->galleries->count() > 0 ? $variant->galleries : $product->galleries;

                        fputcsv($file, [
                            'VAR-' . $variant->id,
                            $product->title . ' - ' . $variant->display_name,
                            strip_tags($product->short_description ?: $product->title),
                            $variant->available_stock > 0 ? 'in stock' : 'out of stock',
                            'new',
                            number_format($variant->regular_price, 2, '.', '') . ' ' . $currency,
                            $variant->discount > 0 ? number_format($variant->final_price, 2, '.', '') . ' ' . $currency : '',
                            url($product->slug),
                            $variant->image ? asset('storage/' . $variant->image) : $product->thumbnail_url,
                            $vGallery->pluck('image_url')->implode(','),
                            $product->brand->name ?? $setup->shop_name,
                            'PRD-' . $product->id,
                            '', // gtin
                            $variant->sku ?? 'VAR-' . $variant->id, // mpn হিসেবে ভ্যারিয়েন্ট SKU
                            $product->mega_categories->first()->name ?? 'General'
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function tiktokCatalogCsv()
    {
        $currentStore = getCurrentCompany();
        if (!$currentStore) abort(401);

        $companyId = $currentStore->company_id;
        $setup = SiteSetting::where('company_id', $companyId)->first();
        $currency = $setup->currency ?? 'BDT';

        $products = Product::where('company_id', $companyId)
            ->where('status', Status::Active->value)
            ->with(['brand', 'variations.attributes.attributeValue', 'galleries'])
            ->get();

        Product::loadCategoriesForCollection($products);

        $fileName = 'tiktok_catalog_' . $companyId . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];


        $columns = [
            'sku_id',
            'product_name',
            'product_url',
            'product_image_url',
            'price',
            'sale_price',
            'availability',
            'condition',
            'brand',
            'description'
        ];

        $callback = function () use ($products, $columns, $setup, $currency) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $commonDescription = strip_tags($product->short_description ?: $product->title);
                $productUrl = url($product->slug);

                if ($product->type === 'single') {
                    // ─── Single Product ───
                    fputcsv($file, [
                        'PRD-' . $product->id, // sku_id
                        $product->title,       // product_name
                        $productUrl,           // product_url
                        $product->thumbnail_url, // product_image_url
                        number_format($product->regular_price, 2, '.', '') . ' ' . $currency, // price
                        $product->discount > 0 ? number_format($product->sale_price, 2, '.', '') . ' ' . $currency : '', // sale_price
                        $product->available_stock > 0 ? 'in stock' : 'out of stock', // availability
                        'new', // condition
                        $product->brand->name ?? $setup->shop_name ?? 'Guitar', // brand
                        $commonDescription // description
                    ]);
                } else {
                    foreach ($product->variations as $variant) {
                        fputcsv($file, [
                            'VAR-' . $variant->id,
                            $product->title . ' - ' . $variant->display_name, // product_name
                            $productUrl, // product_url
                            $variant->image_url ?: $product->thumbnail_url, // product_image_url
                            number_format($variant->regular_price, 2, '.', '') . ' ' . $currency, // price
                            $variant->discount > 0 ? number_format($variant->final_price, 2, '.', '') . ' ' . $currency : '', // sale_price
                            $variant->available_stock > 0 ? 'in stock' : 'out of stock', // availability
                            'new', // condition
                            $product->brand->name ?? $setup->shop_name ?? 'Guitar', // brand
                            $commonDescription // description
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

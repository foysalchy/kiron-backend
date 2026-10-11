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
            "robots_v2_{$companyId}_" . ($allowIndex ? 'index' : 'noindex'),
            now()->addHours(6),
            function () use ($allowIndex) {
                if (!$allowIndex) {
                    return <<<ROBOTS
User-agent: *
Disallow: /
ROBOTS;
                }

                $url = url('/');
                return <<<ROBOTS
User-agent: *
Allow: /
Disallow: /cart/
Disallow: /checkout/
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

        $llmsContent = Cache::remember("llms_txt_{$companyId}", now()->addHours(6), function () use ($currentStore, $companyId) {
            $metaTitle = $currentStore->title ?? $currentStore->shop_name ?? 'Our Shop';
            $metaDescription = $currentStore->description ?? 'Discover our premium products.';
            $shopName = $currentStore->shop_name ?? 'Our Shop';
            $url = url('/');

            $content = "Title: {$metaTitle}\n";
            $content .= "Description: {$metaDescription}\n";
            $content .= "--- \n\n"; // একটি সেপারেটর লাইন

            $content .= "# {$shopName}\n\n";
            $content .= "This file provides guidance for Large Language Models (LLMs) and AI crawlers.\n\n";

            $content .= "## Site Information\n";
            $content .= "- **Base URL:** [{$url}]({$url})\n";
            $content .= "- **Sitemap:** [Sitemap.xml]({$url}/sitemap.xml)\n";
            $content .= "- **Meta Title:** {$metaTitle}\n";
            $content .= "- **Meta Description:** {$metaDescription}\n\n";

            $content .= "## Key Directories\n";
            $content .= "- [Home]({$url})\n";
            $content .= "- [Shop]({$url}/shop)\n";
            $content .= "- [Blogs]({$url}/blog)\n";
            $content .= "- [Brands]({$url}/brands)\n";
            $content .= "- [Login]({$url}/login)\n";
            $content .= "- [Contact Us]({$url}/contact)\n\n";

            $content .= "## Product Categories\n";
            $categories = MegaCategory::where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })->where('status', Status::Active->value)->select('name', 'slug')->get();

            foreach ($categories as $category) {
                $content .= "- [{$category->name}]({$url}/{$category->slug})\n";
            }
            $content .= "\n";

            $content .= "## Guidelines\n";
            $content .= "- Crawlers must respect the instructions in [robots.txt]({$url}/robots.txt).\n";
            $content .= "- Avoid indexing private user dashboards or checkout pages.\n";
            $content .= "- Attribution is required for content used in training sets.";

            return $content;
        });

        return response($llmsContent, 200)->header('Content-Type', 'text/markdown');
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
        $rawCurrency = strtoupper(trim($setup->currency ?? 'BDT'));
        $currencyMap = ['৳' => 'BDT', 'TK' => 'BDT', 'TAKA' => 'BDT', 'TAKA.' => 'BDT'];
        $currency = $currencyMap[$rawCurrency] ?? $rawCurrency;
        
        // Facebook requires a 3-letter ISO 4217 currency code (e.g. BDT, USD). 
        if (strlen($currency) !== 3) {
            $currency = 'BDT';
        }

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
            
            // Output UTF-8 BOM for proper Excel and Meta system compatibility with Bangla characters
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            // Helper: Format Price
            $formatPrice = function ($regular, $sale) use ($currency) {
                $regular = (float) $regular;
                $sale = (float) $sale;
                
                $regularStr = number_format($regular, 2, '.', '') . ' ' . $currency;
                $saleStr = '';
                
                // Only output sale price if valid and strictly less than regular price
                if ($sale > 0 && $sale < $regular) {
                    $saleStr = number_format($sale, 2, '.', '') . ' ' . $currency;
                }
                
                return [$regularStr, $saleStr];
            };

            // Helper: Format and Validate Images
            $formatImages = function ($mainImage, $galleries) {
                $validUrls = [];
                
                $validateUrl = function($url) {
                    $url = trim($url);
                    if (empty($url)) return null;
                    if (filter_var($url, FILTER_VALIDATE_URL) && str_starts_with(strtolower($url), 'https://')) {
                        return $url;
                    }
                    return null;
                };
                
                $mainUrl = $validateUrl($mainImage);
                
                foreach ($galleries as $galleryImg) {
                    $imgUrl = is_object($galleryImg) ? ($galleryImg->image_url ?? '') : $galleryImg;
                    $img = $validateUrl($imgUrl);
                    if ($img && $img !== $mainUrl) {
                        $validUrls[] = $img;
                    }
                }
                
                $validUrls = array_values(array_unique($validUrls));
                
                return [
                    $mainUrl ?? '', 
                    implode(' ', $validUrls) // Space-separated URLs for Facebook/Meta
                ];
            };

            // Helper: Basic Google Product Category mapping
            $getGoogleCategory = function($categoryName) {
                $lower = strtolower(trim($categoryName));
                if (str_contains($lower, 'diaper')) {
                    return 'Baby & Toddler > Diapering > Diapers';
                }
                if (str_contains($lower, 'baby')) {
                    return 'Baby & Toddler';
                }
                if (str_contains($lower, 'electronics')) {
                    return 'Electronics';
                }
                return $categoryName;
            };

            // Helper: Clean Description
            $cleanDescription = function($html) {
                $text = strip_tags($html);
                $text = preg_replace('/\s+/', ' ', $text); // Remove excessive whitespace/newlines
                return trim($text);
            };

            foreach ($products as $product) {
                $productTitle = trim($product->title);
                $description = $cleanDescription($product->short_description ?: $product->title);
                $categoryName = $product->mega_categories->first()->name ?? 'General';
                $googleCategory = $getGoogleCategory($categoryName);
                $brandName = $product->brand->name ?? $setup->shop_name ?? 'Generic';
                $link = url($product->slug);

                if ($product->type === 'single') {
                    // ─── Single Product Row ───
                    [$price, $salePrice] = $formatPrice($product->regular_price, $product->sale_price);
                    [$mainImage, $additionalImages] = $formatImages($product->thumbnail_url, $product->galleries);
                    
                    fputcsv($file, [
                        'PRD-' . $product->id,
                        $productTitle,
                        $description,
                        $product->available_stock > 0 ? 'in stock' : 'out of stock',
                        'new',
                        $price,
                        $salePrice,
                        $link,
                        $mainImage,
                        $additionalImages,
                        $brandName,
                        'PRD-' . $product->id,
                        '', // gtin
                        'PRD-' . $product->id, // mpn
                        $googleCategory
                    ]);
                } else {
                    // ─── Variable Product Rows ───
                    foreach ($product->variations as $variant) {
                        $vGallery = $variant->galleries->count() > 0 ? $variant->galleries : $product->galleries;
                        
                        // Use existing image_url accessor for variation, fallback to product thumbnail
                        $variantImage = $variant->image_url ?: $product->thumbnail_url;
                        
                        [$price, $salePrice] = $formatPrice($variant->regular_price, $variant->final_price);
                        [$mainImage, $additionalImages] = $formatImages($variantImage, $vGallery);

                        fputcsv($file, [
                            'VAR-' . $variant->id,
                            $productTitle . ' - ' . $variant->display_name,
                            $description,
                            $variant->available_stock > 0 ? 'in stock' : 'out of stock',
                            'new',
                            $price,
                            $salePrice,
                            $link,
                            $mainImage,
                            $additionalImages,
                            $brandName,
                            'PRD-' . $product->id,
                            '', // gtin
                            $variant->sku ?? 'VAR-' . $variant->id, // mpn
                            $googleCategory
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

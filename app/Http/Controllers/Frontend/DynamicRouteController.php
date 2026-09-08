<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\MegaCategory;
use App\Models\SubCategory;
use App\Models\MiniCategory;
use App\Models\Page;
use App\Models\Brand;
use App\Models\LandingPage;
use App\Models\Blog;

class DynamicRouteController extends Controller
{
    public function resolve(Request $request, $slug)
    {
        // One single optimized DB query checking all possible tables using UNION ALL
        $slugTypeQuery = DB::query()
            ->selectRaw("'product' as type")
            ->from((new Product)->getTable())
            ->where('slug', $slug)
            ->unionAll(DB::query()->selectRaw("'mega_category'")->from((new MegaCategory)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'sub_category'")->from((new SubCategory)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'mini_category'")->from((new MiniCategory)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'page'")->from((new Page)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'brand'")->from((new Brand)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'landing_page'")->from((new LandingPage)->getTable())->where('slug', $slug))
            ->unionAll(DB::query()->selectRaw("'blog'")->from((new Blog)->getTable())->where('slug', $slug));

        $result = clone $slugTypeQuery;
        $resolvedType = $result->first()->type ?? 'not_found';

        // Delegate to the appropriate controller based on the resolved type
        switch ($resolvedType) {
            case 'product':
                return app(ProductController::class)->productDetails($request, $slug);

            case 'mega_category':
                return app(ProductController::class)->categoryProducts($request, $slug);

            case 'sub_category':
                $subCategory = SubCategory::where('slug', $slug)->first();
                $megaSlug = $subCategory->megaCategory->slug ?? 'category';
                return app(ProductController::class)->subcategoryProducts($request, $megaSlug, $slug);

            case 'mini_category':
                $miniCategory = MiniCategory::where('slug', $slug)->first();
                $subSlug = $miniCategory->subCategory->slug ?? 'subcategory';
                $megaSlug = $miniCategory->subCategory->megaCategory->slug ?? 'category';
                return app(ProductController::class)->minicategoryProducts($request, $megaSlug, $subSlug, $slug);

            case 'page':
                return app(AboutController::class)->showPage($slug);

            case 'brand':
                return app(ProductController::class)->brandProducts($request, $slug);

            case 'landing_page':
                return app(LandingController::class)->index($request, $slug);

            case 'blog':
                return app(BlogController::class)->blogDetails($request, $slug);

            default:
                abort(404);
        }
    }
}

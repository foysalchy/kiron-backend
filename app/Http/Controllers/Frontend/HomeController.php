<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\KnowledgeBase;
use App\Models\MegaCategory;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductReview;
use App\Models\Slider;
use App\Models\Subscription;
use App\Models\SystemPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends FrontendController
{
    public function index()
    {
        $companyId = $this->company_id;
        // ক্যাশ লাইফটাইম (৬ ঘণ্টা)
        $ttl = now()->addHours(6);

        // ১. হোমপেজ মেটা/সেটিংস ডেটা
        $homePageData = Cache::remember("home_page_data_{$companyId}", $ttl, function () use ($companyId) {
            return SystemPage::where('company_id', $companyId)
                ->where('page_type', 'home')
                ->select('id', 'company_id', 'title', 'description', 'meta_title', 'meta_description', 'meta_keywords')
                ->first();
        });

        // ২. এফএকিউ (FAQs)
        $faqs = Cache::remember("home_faqs_{$companyId}", $ttl, function () use ($companyId) {
            return KnowledgeBase::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->select('id', 'title', 'content')->get();
        });

        // ৩. ক্যাটাগরি এবং সাব-ক্যাটাগরি
        $categories = Cache::remember("home_categories_{$companyId}", $ttl, function () use ($companyId) {
            return MegaCategory::where('company_id', $companyId)
                ->select('id', 'name', 'company_id', 'slug', 'image')
                ->with('subCategories:id,mega_category_id,name,slug', 'subCategories.miniCategories:id,sub_category_id,name,slug')->get();
        });

        // ৪. লেটেস্ট অফার
        $latestOffers = Cache::remember("home_latest_offers_{$companyId}", $ttl, function () use ($companyId) {
            return Product::where('company_id', $companyId)
                ->with(['brand:id,company_id,name,slug,logo', 'variations'])
                ->where('status', Status::Active->value)
                ->where(function ($q) {
                    $q->where('discount', '>', 0)
                        ->orWhereHas('variations', function ($vq) {
                            $vq->where('discount', '>', 0);
                        });
                })
                // নোট: 'product_variations' এবং 'product_id' — আপনার আসল variation টেবিল/কলাম নাম অনুযায়ী বসান
                ->selectRaw('products.*, (
                SELECT MAX(
                    CASE WHEN pv.discount_type = \'percent\'
                        THEN (pv.regular_price * pv.discount / 100)
                        ELSE pv.discount
                    END
                )
                FROM product_variations pv
                WHERE pv.product_id = products.id
                  AND pv.discount > 0
            ) as variation_max_discount_amount')
                ->selectRaw("
                CASE WHEN products.discount_type = 'percent'
                    THEN (products.regular_price * products.discount / 100)
                    ELSE products.discount
                END as product_discount_amount
            ")
                ->orderByRaw('GREATEST(COALESCE(product_discount_amount, 0), COALESCE(variation_max_discount_amount, 0)) DESC')
                ->orderByDesc('created_at')
                ->take(12)
                ->get();
        });

        $newArrivals = Cache::remember("home_new_arrivals_{$companyId}", $ttl, function () use ($companyId) {
            return Product::where('company_id', $companyId)
                ->with(['brand:id,company_id,name,slug,logo', 'variations.attributes.attributeValue'])
                ->where('status', Status::Active->value)
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->take(8)
                ->get();
        });

        //  (N+1 )
        $productGroups = Cache::remember("home_product_groups_{$companyId}", $ttl, function () use ($companyId) {
            return ProductGroup::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->where('is_frontend', 1)
                ->select('id', 'name', 'slug', 'product_ids')
                ->get()
                ->map(function ($group) {
                    $group->products = Product::whereIn('id', $group->product_ids ?? [])
                        ->with(['variations'])
                        ->where('status', Status::Active->value)
                        ->withCount('reviews')
                        ->withAvg('reviews', 'rating')
                        ->take(6)
                        ->get();

                    return $group;
                })
                ->filter(fn($group) => $group->products->count() > 0);
        });

        // ৭. ব্র্যান্ডস
        $brands = Cache::remember("home_brands_{$companyId}", $ttl, function () use ($companyId) {
            return Brand::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->select('id', 'company_id', 'name', 'slug', 'logo')
                ->latest()
                ->take(10)
                ->get();
        });

        // ৮. পপুলার প্রোডাক্টস
        $popularProducts = Cache::remember("home_popular_products_{$companyId}", $ttl, function () use ($companyId) {
            return Product::where('company_id', $companyId)->with(['brand:id,company_id,name,slug,logo', 'variations'])
                ->select(
                    'id',
                    'company_id',
                    'brand_id',
                    'title',
                    'slug',
                    'thumbnail',
                    'regular_price',
                    'purchase_price',
                    'discount',
                    'discount_type',
                    'available_stock',
                    'type'
                )
                ->where('status', Status::Active->value)
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->withSum('orderDetails as total_sales', 'quantity')
                ->orderByDesc('total_sales')
                ->take(12)
                ->get();
        });

        // ৯. অল প্রোডাক্টস (লেটেস্ট ১২টি)
        $allProducts = Cache::remember("home_all_products_{$companyId}", $ttl, function () use ($companyId) {
            return Product::where('company_id', $companyId)
                ->with(['brand:id,company_id,name,slug,logo', 'variations'])
                ->select(
                    'id',
                    'company_id',
                    'brand_id',
                    'title',
                    'slug',
                    'thumbnail',
                    'regular_price',
                    'purchase_price',
                    'discount',
                    'discount_type',
                    'available_stock',
                    'manage_stock',
                    'type'
                )
                ->where('status', Status::Active->value)
                ->latest()
                ->take(12)
                ->get();
        });

        // ১০. স্লাইডার্স (একটি কোয়েরি দিয়ে এনে নিচে ফিল্টার করা হয়েছে)
        $allSliders = Cache::remember("home_sliders_{$companyId}", $ttl, function () use ($companyId) {
            return Slider::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->select('id', 'company_id', 'title', 'image', 'url', 'placement')
                ->get();
        });

        $mainSliders = $allSliders->where('placement', 'hero');

        $sidebarSliders = $allSliders->where('placement', 'right');
        $middleSliders = $allSliders->where('placement', 'middle')->take(3);
        // ১১. অল রিভিউস
        $allReviews = Cache::remember("home_reviews_{$companyId}", $ttl, function () use ($companyId) {
            return ProductReview::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->with('customer')
                ->latest()
                ->get();
        });

        // ভিউতে ডেটা পাঠানো
        return $this->view('frontend.home', compact(
            'categories',
            'newArrivals',
            'brands',
            'popularProducts',
            'productGroups',
            'allSliders',
            'mainSliders',
            'sidebarSliders',
            'middleSliders',
            'allProducts',
            'allReviews',
            'faqs',
            'latestOffers',
            'homePageData'
        ));
    }
    public function allCategories()
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        $categories = Cache::remember("home_categories_{$companyId}", $ttl, function () use ($companyId) {
            return MegaCategory::where('company_id', $companyId)
                ->select('id', 'name', 'company_id', 'slug', 'image')
                ->with('subCategories:id,mega_category_id,name,slug', 'subCategories.miniCategories:id,sub_category_id,name,slug')
                ->get();
        });

        return $this->view('frontend.allcategories', compact('categories'));
    }
    public function filterSubCategory(Request $request)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        $subId = (int)$request->sub_id;
        return Cache::remember("filter_sub_cat_{$companyId}_{$subId}", $ttl, function () use ($subId, $companyId) {

            $products = Product::where('status', Status::Active->value)
                ->where('company_id', $companyId)
                ->whereJsonContains('sub_category_ids', $subId)
                ->select(['id', 'company_id', 'title', 'slug', 'thumbnail', 'sale_price', 'regular_price', 'discount', 'type', 'available_stock', 'manage_stock'])
                ->with(['variations'])
                ->latest()->take(6)->get();

            $html = '';
            $template = $this->template;

            foreach ($products as $product) {
                $html .= view("components.{$template}.product-card", compact('product'))->render();
            }

            if ($html == '') {
                return '<div class="col-span-full py-10 text-center text-gray-400">কোনো পণ্য পাওয়া যায়নি।</div>';
            }

            return $html;
        });
    }
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $exists = Subscription::where('email', $request->email)
            ->where('company_id', $this->company_id)
            ->first();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'You are already subscribed!'], 422);
        }

        Subscription::create([
            'company_id' => $this->company_id,
            'email' => $request->email,
            'status' => Status::Active->value,
        ]);

        return response()->json(['success' => true, 'message' => 'Thanks for subscribing!']);
    }
}

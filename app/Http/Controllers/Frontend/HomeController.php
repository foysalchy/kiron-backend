<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\KnowledgeBase;
use App\Models\MegaCategory;
use App\Models\MenuSetting;
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
        $template = $this->template;

        // কোন টেমপ্লেটে কোন ডেটা প্রয়োজন তার একটি ম্যাপিং
        $requirements = [
            'template1' => ['homePageData', 'faqs', 'categories', 'featureCategory', 'newArrivals', 'productGroups', 'brands', 'popularProducts', 'allProducts', 'mainSliders', 'sidebarSliders', 'middleSliders'],
            'template2' => ['homePageData', 'categories', 'featureCategory', 'productGroups', 'allProducts', 'mainSliders', 'sidebarSliders'],
            'template3' => ['categories', 'featureCategory', 'productGroups', 'allProducts', 'mainSliders', 'middleSliders', 'allReviews'],
            'template4' => ['homePageData', 'featureCategory', 'latestOffers', 'productGroups', 'popularProducts', 'mainSliders', 'sidebarSliders'],
            'template5' => ['faqs', 'categories', 'featureCategory', 'latestOffers', 'popularProducts', 'mainSliders'],
        ];

        // যদি কোনো কারণে টেমপ্লেট ম্যাচ না করে তবে ডিফল্ট হিসেবে template1 ধরতে পারি
        $reqs = $requirements[$template] ?? $requirements['template1'];

        // প্রোডাক্ট ফেচ করার জন্য নির্দিষ্ট এবং অপ্টিমাইজড কলামগুলো
        $productSelect = [
            'products.id',
            'products.company_id',
            'products.brand_id',
            'products.title',
            'products.slug',
            'products.regular_price',
            'products.purchase_price',
            'products.discount',
            'products.discount_type',
            'products.available_stock',
            'products.manage_stock',
            'products.type',
            'products.status',
            'products.short_description',
            'products.created_at'
        ];

        // টেমপ্লেট অনুযায়ী শুধু প্রয়োজনীয় ইমেজ ফাইল লোড করা
        if ($template === 'template4') {
            // template4 এ thumbnail_95 এবং thumbnail_310 উভয়ই ব্যবহৃত হয়
            $productSelect[] = 'products.thumbnail_310';
            $productSelect[] = 'products.thumbnail_95';
        } else {
            // অন্যান্য সব টেমপ্লেটে thumbnail_310 ব্যবহৃত হয়
            $productSelect[] = 'products.thumbnail_310';
        }

        // ডিফল্ট ভ্যারিয়েবল (যাতে ভিউতে undefined variable error না দেয়)
        $homePageData = null;
        $faqs = collect();
        $categories = collect();
        $featureCategory = null;
        $latestOffers = collect();
        $newArrivals = collect();
        $productGroups = collect();
        $brands = collect();
        $popularProducts = collect();
        $allProducts = collect();
        $allSliders = collect();
        $mainSliders = collect();
        $sidebarSliders = collect();
        $middleSliders = collect();
        $allReviews = collect();


        // ১. হোমপেজ মেটা/সেটিংস ডেটা
        if (in_array('homePageData', $reqs)) {
            $homePageData = Cache::remember("home_page_data_{$companyId}", $ttl, function () use ($companyId) {
                return SystemPage::where('company_id', $companyId)
                    ->where('page_type', 'home')
                    ->select('id', 'company_id', 'title', 'description', 'meta_title', 'meta_description', 'meta_keywords')
                    ->first();
            });
        }

        // ২. এফএকিউ (FAQs)
        if (in_array('faqs', $reqs)) {
            $faqs = Cache::remember("home_faqs_{$companyId}", $ttl, function () use ($companyId) {
                return KnowledgeBase::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->select('id', 'title', 'content')->get();
            });
        }

        // ৩. ক্যাটাগরি এবং সাব-ক্যাটাগরি
        if (in_array('categories', $reqs)) {
            $categories = Cache::remember("home_categories_{$companyId}", $ttl, function () use ($companyId) {
                $cats = MegaCategory::where('company_id', $companyId)
                    ->select('id', 'name', 'company_id', 'slug', 'image')
                    ->with('subCategories:id,mega_category_id,name,slug', 'subCategories.miniCategories:id,sub_category_id,name,slug')->get();

                $cats->map(function ($cat) use ($companyId) {
                    $cat->product_count = \App\Models\Product::where('status', 1)
                        ->where('company_id', $companyId)
                        ->where(function ($q) use ($cat) {
                            $q->whereJsonContains('mega_category_ids', (int) $cat->id)
                                ->orWhereJsonContains('mega_category_ids', (string) $cat->id);
                        })
                        ->count();
                    return $cat;
                });

                return $cats;
            });
        }

        if (in_array('featureCategory', $reqs)) {
            $featureCategory = Cache::remember("active_feature_category_{$companyId}", $ttl, function () use ($companyId) {
                $menuSetting = MenuSetting::where('company_id', $companyId)
                    ->where('type', MenuSetting::TYPE_FEATURE_CATEGORY)
                    ->where('status', Status::Active->value)
                    ->latest()
                    ->first();
                if (! $menuSetting) {
                    return null;
                }

                return [
                    'id'    => $menuSetting->id,
                    'name'  => $menuSetting->name,
                    'items' => $menuSetting->resolvedItems(), // fresh name/slug/image/link per item
                ];
            });
        }

        // ৪. লেটেস্ট অফার
        if (in_array('latestOffers', $reqs)) {
            $latestOffers = Cache::remember("home_latest_offers_{$companyId}", $ttl, function () use ($companyId, $productSelect) {
                return Product::where('company_id', $companyId)
                    ->select($productSelect)
                    ->with(['brand:id,company_id,name,slug,logo', 'variations'])
                    ->where('status', Status::Active->value)
                    ->where(function ($q) {
                        $q->where('discount', '>', 0)
                            ->orWhereHas('variations', function ($vq) {
                                $vq->where('discount', '>', 0);
                            });
                    })
                    ->selectRaw('(
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
        }

        if (in_array('newArrivals', $reqs)) {
            $newArrivals = Cache::remember("home_new_arrivals_{$companyId}", $ttl, function () use ($companyId, $productSelect) {
                return Product::where('company_id', $companyId)
                    ->select($productSelect)
                    ->with(['brand:id,company_id,name,slug,logo', 'variations.attributes.attributeValue'])
                    ->where('status', Status::Active->value)
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating')
                    ->latest()
                    ->take(8)
                    ->get();
            });
        }

        if (in_array('productGroups', $reqs)) {
            $productGroups = Cache::remember("home_product_groups_{$companyId}", $ttl, function () use ($companyId, $productSelect) {
                return ProductGroup::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->where('is_frontend', 1)
                    ->select('id', 'name', 'slug', 'product_ids')
                    ->get()
                    ->map(function ($group) use ($productSelect) {
                        $group->products = Product::whereIn('id', $group->product_ids ?? [])
                            ->select($productSelect)
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
        }

        // ৭. ব্র্যান্ডস
        if (in_array('brands', $reqs)) {
            $brands = Cache::remember("home_brands_{$companyId}", $ttl, function () use ($companyId) {
                return Brand::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->select('id', 'company_id', 'name', 'slug', 'logo')
                    ->latest()
                    ->take(10)
                    ->get();
            });
        }

        // ৮. পপুলার প্রোডাক্টস
        if (in_array('popularProducts', $reqs)) {
            $popularProducts = Cache::remember("home_popular_products_{$companyId}", $ttl, function () use ($companyId, $productSelect) {
                return Product::where('company_id', $companyId)
                    ->select($productSelect)
                    ->with(['brand:id,company_id,name,slug,logo', 'variations'])
                    ->where('status', Status::Active->value)
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating')
                    ->withSum('orderDetails as total_sales', 'quantity')
                    ->orderByDesc('total_sales')
                    ->take(12)
                    ->get();
            });
        }

        // ৯. অল প্রোডাক্টস (লেটেস্ট ১২টি)
        if (in_array('allProducts', $reqs)) {
            $allProducts = Cache::remember("home_all_products_{$companyId}", $ttl, function () use ($companyId, $productSelect) {
                return Product::where('company_id', $companyId)
                    ->select($productSelect)
                    ->with(['brand:id,company_id,name,slug,logo', 'variations'])
                    ->where('status', Status::Active->value)
                    ->latest()
                    ->take(12)
                    ->get();
            });
        }

        // ১০. স্লাইডার্স
        if (in_array('mainSliders', $reqs) || in_array('sidebarSliders', $reqs) || in_array('middleSliders', $reqs)) {
            $allSliders = Cache::remember("home_sliders_{$companyId}", $ttl, function () use ($companyId) {
                return Slider::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->select('id', 'company_id', 'title', 'image', 'mobile_image', 'url', 'placement')
                    ->get();
            });

            if (in_array('mainSliders', $reqs)) {
                $mainSliders = $allSliders->where('placement', 'hero');
            }
            if (in_array('sidebarSliders', $reqs)) {
                $sidebarSliders = $allSliders->where('placement', 'right');
            }
            if (in_array('middleSliders', $reqs)) {
                $middleSliders = $allSliders->where('placement', 'middle')->take(3);
            }
        }

        // ১১. অল রিভিউস
        if (in_array('allReviews', $reqs)) {
            $allReviews = Cache::remember("home_reviews_{$companyId}", $ttl, function () use ($companyId) {
                return ProductReview::where('company_id', $companyId)
                    ->where('status', Status::Active->value)
                    ->with('customer')
                    ->latest()
                    ->get();
            });
        }

        // ভিউতে ডেটা পাঠানো
        return $this->view('frontend.home', compact(
            'categories',
            'featureCategory',
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
            $cats = MegaCategory::where('company_id', $companyId)
                ->select('id', 'name', 'company_id', 'slug', 'image')
                ->with('subCategories:id,mega_category_id,name,slug', 'subCategories.miniCategories:id,sub_category_id,name,slug')
                ->get();

            $cats->map(function ($cat) use ($companyId) {
                $cat->product_count = \App\Models\Product::where('status', 1)
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($cat) {
                        $q->whereJsonContains('mega_category_ids', (int) $cat->id)
                            ->orWhereJsonContains('mega_category_ids', (string) $cat->id);
                    })
                    ->count();
                return $cat;
            });

            return $cats;
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
                ->select(['id', 'company_id', 'title', 'slug', 'thumbnail', 'thumbnail_310', 'thumbnail_95', 'sale_price', 'regular_price', 'discount', 'type', 'available_stock', 'manage_stock'])
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

<?php

namespace App\Http\Controllers\Saas;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\CustomerReview;
use App\Models\KnowledgeBase;
use App\Models\MasterBrand;
use App\Models\MasterDemo;
use App\Models\MasterFeature;
use App\Models\PricingPackage;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    // গ্লোবাল ক্যাশ লাইফটাইম (৬ ঘণ্টা)
    protected $ttl;

    public function __construct()
    {
        $this->ttl = now()->addHours(6);
    }

    public function home()
    {
        // ১. স্লাইডার্স ক্যাশ
        $sliders = Cache::remember('saas_home_sliders', $this->ttl, function () {
            return Slider::withoutCompanyScope()
                ->where('status', Status::Active->value)
                ->where('placement', 'hero')
                ->whereNull('company_id')
                ->orderByDesc('id')
                ->get();
        });

        // ২. ব্র্যান্ডস ক্যাশ
        $brands = Cache::remember('saas_home_brands', $this->ttl, function () {
            return MasterBrand::where('status', Status::Active->value)
                ->latest()
                ->get();
        });

        // ৩. টপ ফিচার্স ক্যাশ
        $topFeatures = Cache::remember('saas_home_top_features', $this->ttl, function () {
            return MasterFeature::where('status', Status::Active->value)
                ->where('placement', 1)
                ->select('title', 'subtitle', 'icon', 'slug')
                ->get();
        });

        // ৪. হোয়াই চুজ আস ক্যাশ
        $whyChooseUs = Cache::remember('saas_home_why_choose_us', $this->ttl, function () {
            return MasterFeature::where('status', Status::Active->value)
                ->where('placement', 2)
                ->latest()
                ->select('title', 'description', 'image')
                ->get();
        });

        // ৫. রিভিউ স্ট্যাটস ক্যাশ
        $reviewStats = Cache::remember('saas_home_review_stats', $this->ttl, function () {
            $stats = CustomerReview::where('status', Status::Active->value)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
                ->first();

            return [
                'avgRating' => round($stats->avg_rating ?? 0, 1),
                'totalReviews' => $stats->total_reviews ?? 0
            ];
        });

        $avgRating = $reviewStats['avgRating'];
        $totalReviews = $reviewStats['totalReviews'];

        // ৬. অল রিভিউস ক্যাশ
        $allReviews = Cache::remember('saas_home_all_reviews', $this->ttl, function () {
            return CustomerReview::where('status', Status::Active->value)
                ->latest()
                ->get();
        });

        // ৭. ডেমোস ক্যাশ
        $demos = Cache::remember('saas_home_demos', $this->ttl, function () {
            return MasterDemo::where('status', Status::Active->value)
                ->latest()
                ->get();
        });

        // ৮. লেটেস্ট ব্লগস ক্যাশ (হোমপেজের জন্য ৩টি)
        $blogs = Cache::remember('saas_home_latest_blogs', $this->ttl, function () {
            return Blog::withoutCompanyScope()
                ->whereNull('company_id')
                ->where('status', Status::Active->value)
                ->latest()
                ->take(3)
                ->get(['id', 'title', 'slug', 'images', 'created_at', 'short']);
        });

        // ৯. প্রাইসিং প্ল্যানস ক্যাশ
        $pricingPlans = Cache::remember('saas_home_pricing_plans', $this->ttl, function () {
            return PricingPackage::with('tiers')
                ->where('status', Status::Active->value)
                ->take(4)
                ->get();
        });

        // ১০. এফএকিউ ক্যাশ
        $faqs = Cache::remember('saas_home_faqs', $this->ttl, function () {
            return KnowledgeBase::where('status', 1)
                ->whereNull('company_id')
                ->orderBy('id', 'asc')
                ->get();
        });

        return view('saas.frontend.index', compact(
            'sliders',
            'faqs',
            'brands',
            'topFeatures',
            'whyChooseUs',
            'avgRating',
            'totalReviews',
            'demos',
            'allReviews',
            'blogs',
            'pricingPlans'
        ));
    }

    public function features()
    {
        // প্যাজিনেশনের জন্য পেজ নম্বর অনুযায়ী আলাদা ক্যাশ কী জেনারেট হবে
        $page = request()->get('page', 1);

        $allFeatures = Cache::remember("saas_features_page_{$page}", $this->ttl, function () {
            return MasterFeature::where('status', Status::Active->value)
                ->where('placement', 1)
                ->select('id', 'slug', 'title', 'icon')
                ->paginate(40);
        });

        return view('saas.frontend.featureList', compact('allFeatures'));
    }

    public function featureDetails($slug)
    {
        $feature = Cache::remember("saas_feature_details_{$slug}", $this->ttl, function () use ($slug) {
            return MasterFeature::where('slug', $slug)
                ->where('status', Status::Active->value)
                ->firstOrFail();
        });

        // cache ছাড়া সরাসরি - lightweight query, cache করার দরকার নাই
        $otherFeatures = MasterFeature::where('status', Status::Active->value)
            ->where('id', '!=', $feature->id)
            ->where('placement', 1)
            ->take(4)
            ->select('id', 'slug', 'title', 'icon')
            ->get();

        return view('saas.frontend.featureDetails', compact('feature', 'otherFeatures'));
    }

    public function blogPosts()
    {
        $page = request()->get('page', 1);

        $blogPosts = Cache::remember("saas_blogs_page_{$page}", $this->ttl, function () {
            return Blog::withoutCompanyScope()
                ->whereNull('company_id')
                ->where('status', Status::Active->value)
                ->latest()
                ->select('id', 'title', 'slug', 'images', 'created_at', 'short')
                ->paginate(9);
        });

        return view('saas.frontend.blogList', compact('blogPosts'));
    }

    public function blogPostDetails($slug)
    {
        $blogPost = Cache::remember("saas_blog_details_{$slug}", $this->ttl, function () use ($slug) {
            return Blog::withoutCompanyScope()
                ->whereNull('company_id')
                ->where('slug', $slug)
                ->where('status', Status::Active->value)
                ->firstOrFail();
        });

        $otherBlogPosts = Blog::withoutCompanyScope()
            ->with('company')
            ->where('status', Status::Active->value)
            ->where('id', '!=', $blogPost->id)
            ->latest()
            ->take(4)
            ->get(['id', 'title', 'slug', 'images', 'created_at', 'short']);

        return view('saas.frontend.blogDetails', compact('blogPost', 'otherBlogPosts'));
    }

    public function faqList()
    {
        $faqs = Cache::remember('saas_faqs_list', $this->ttl, function () {
            return KnowledgeBase::where('status', Status::Active->value)
                ->whereNull('company_id')
                ->latest()
                ->get();
        });

        return view('saas.frontend.faqList', compact('faqs'));
    }

    public function packageList()
    {
        $pricingPlans = Cache::remember('saas_packages_list', $this->ttl, function () {
            return PricingPackage::where('status', Status::Active->value)->get();
        });

        return view('saas.frontend.pricingList', compact('pricingPlans'));
    }

    public function contact()
    {
        return view('saas.frontend.contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create([
            'company_id' => null,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'is_read'    => 0,
        ]);

        return back()->with('success', 'Your message has been received. Thank you!');
    }
}

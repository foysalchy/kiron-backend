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

class IndexController extends Controller
{
    public function home()
    {
        $sliders = Slider::withoutCompanyScope()
            ->where('status', Status::Active->value)
            ->where('placement', 'hero')
            ->whereNull('company_id')
            ->latest()
            ->get();
        $brands = MasterBrand::where('status', Status::Active->value)
            ->latest()
            ->get();

        $topFeatures = MasterFeature::where('status', Status::Active->value)
            ->where('placement', 1)
       
            ->select('title', 'subtitle', 'icon', 'slug')
            ->get();

        $whyChooseUs = MasterFeature::where('status', Status::Active->value)
            ->where('placement', 2)
            ->latest()
            ->select('title', 'description', 'image')
            ->get();

        $reviewsQuery = CustomerReview::where('status', Status::Active->value);

        $avgRating = $reviewsQuery->avg('rating') ?: 0;
        $totalReviews = $reviewsQuery->count();

        $allReviews = CustomerReview::where('status', Status::Active->value)->latest()->get();

        $demos = MasterDemo::where('status', Status::Active->value)
            ->latest()
            ->get();

        $blogs = Blog::withoutCompanyScope()
            ->with('company')
            ->where('status', Status::Active->value)
            ->latest()
            ->take(3)
            ->get();
        $pricingPlans = PricingPackage::where('status', Status::Active->value)
            ->take(4)
            ->get();
        return view('saas.frontend.index', compact(
            'sliders',
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
        $allFeatures = MasterFeature::where('status', Status::Active->value)
            ->where('placement', 1)
             
            ->paginate(40);

        return view('saas.frontend.featureList', compact('allFeatures'));
    }
    public function featureDetails($slug)
    {
        $feature = MasterFeature::where('slug', $slug)
            ->where('status', Status::Active->value)
            ->firstOrFail();

        $otherFeatures = MasterFeature::where('status', Status::Active->value)
            ->where('id', '!=', $feature->id)
            ->where('placement', 1)
            ->take(4)
            ->get();

        return view('saas.frontend.featureDetails', compact('feature', 'otherFeatures'));
    }
    public function blogPosts()
    {
        $blogPosts = Blog::withoutCompanyScope()
            ->with('company')
            ->where('status', Status::Active->value)
            ->latest()
            ->paginate(3);

        return view('saas.frontend.blogList', compact('blogPosts'));
    }
    public function blogPostDetails($slug)
    {
        $blogPost = Blog::withoutCompanyScope()
            ->with('company')
            ->where('slug', $slug)
            ->where('status', Status::Active->value)
            ->firstOrFail();

        $otherBlogPosts = Blog::withoutCompanyScope()
            ->with('company')
            ->where('status', Status::Active->value)
            ->where('id', '!=', $blogPost->id)
            ->latest()
            ->take(4)
            ->get();
 
        return view('saas.frontend.blogDetails', compact('blogPost', 'otherBlogPosts'));
    }
    public function faqList()
    {
        $faqs = KnowledgeBase::where('status', Status::Active->value)
            ->latest()
            ->get();
        return view('saas.frontend.faqList', compact('faqs'));
    }
    public function packageList()
    {
        $pricingPlans = PricingPackage::where('status', Status::Active->value)
          
            ->get();
        return view('saas.frontend.pricingList', compact('pricingPlans'));
    }
    public function contact()
    {
        return view('saas.frontend.contact');
    }
    public function send(Request $request)
    {
        // ১. ভ্যালিডেশন
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

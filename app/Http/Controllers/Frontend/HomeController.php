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
use Illuminate\Http\Request;

class HomeController extends FrontendController
{
    public function index()
    {
        $faqs = KnowledgeBase::active()
            ->get();

        $categories = MegaCategory::with('subCategories.miniCategories')->get();

        $latestOffers = Product::with(['brand', 'variations'])
            ->where('discount', '>', 0)
            ->where('status', Status::Active->value)
            ->latest()
            ->take(12)
            ->get();

        $newArrivals = Product::with(['brand', 'variations.attributes.attributeValue'])
            ->where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')->latest()->take(8)->get();
        // \Log::info($newArrivals);
        //for product groups
        $productGroups = ProductGroup::where('status', Status::Active->value)
            ->where('is_frontend', 1)
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

        $brands = Brand::where('status', Status::Active->value)->latest()->take(10)->get();

        $popularProducts = Product::with(['brand', 'variations'])
            ->where('status', Status::Active->value)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withSum('orderDetails as total_sales', 'quantity')
            ->orderByDesc('total_sales')
            ->take(12)
            ->get();
        $allProducts = Product::with(['brand', 'variations'])
            ->where('status', Status::Active->value)
            ->latest()
            ->take(12)
            ->get();

        $allSliders = Slider::where('status', Status::Active->value)->get();
        $mainSliders = $allSliders->where('placement', 'hero');
        $sidebarSliders = $allSliders->where('placement', 'right');
        $middleSliders = $allSliders->where('placement', 'middle')->take(2);

        $allReviews = ProductReview::where('company_id', $this->company_id)
            ->where('status', Status::Active->value)
            ->with('customer')
            ->latest()
            ->get();
        return  $this->view(
            'frontend.home',
            compact(
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
                'latestOffers'
            )
        );
    }
    public function filterSubCategory(Request $request)
    {
        $subId = (int)$request->sub_id;
        $products = Product::where('status', Status::Active->value)
            ->whereJsonContains('sub_category_ids', $subId)
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

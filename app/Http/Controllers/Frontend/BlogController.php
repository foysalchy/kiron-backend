<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request, $store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $availableTags = Blog::active()
            ->whereNotNull('meta_keywords')
            ->get()
            ->pluck('meta_keywords')
            ->flatten()
            ->unique()
            ->filter()
            ->values();

        $query = Blog::with('user')->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->where('meta_keywords', 'like', "%{$tag}%");
        }

        $blogs = $query->latest()->paginate(6)->withQueryString();

        return view($template . '.frontend.blog', compact('blogs', 'availableTags'));
    }
    public function blogDetails($store, $slug)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        // স্লাগ থেকে স্পেস এবং ড্যাশ উভয় ভার্সন তৈরি করা
        $slugWithDash = str_replace(['%20', ' '], '-', $slug);
        $slugWithSpace = str_replace(['%20', '-'], ' ', $slug);

        $blog = Blog::with('user')
            ->where(function($query) use ($slugWithDash, $slugWithSpace, $slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug', $slugWithDash)
                    ->orWhere('slug', $slugWithSpace);
            })
            ->where('company_id', $company->company_id ?? $company->id) // এই সাবডোমেনের ব্লগ কি না চেক
            ->active()
            ->firstOrFail();

        // সম্পর্কিত পোস্ট (একই কোম্পানির হতে হবে)
        $relatedPosts = Blog::active()
            ->where('company_id', $company->company_id ?? $company->id)
            ->where('id', '!=', $blog->id)
            ->latest()->take(3)->get();

        // পপুলার ট্যাগ (একই কোম্পানির হতে হবে)
        $popularTags = Blog::active()
            ->where('company_id', $company->company_id ?? $company->id)
            ->whereNotNull('meta_keywords')
            ->get()->pluck('meta_keywords')->flatten()->unique()->filter()->values();

        return view($template . '.frontend.blogDetails', compact('blog', 'relatedPosts', 'popularTags'));
    }
}

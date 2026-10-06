<?php

namespace AppHttpControllersFrontend;

use AppEnumsStatus;
use AppHttpControllersController;
use AppModelsBlog;
use IlluminateHttpRequest;
use IlluminateSupportFacadesCache;

class BlogController extends FrontendController
{
    public function index(Request $request)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);
        $query = Blog::with('user:id,name')
            ->select('id', 'user_id', 'company_id', 'title', 'slug', 'short', 'images', 'created_at')
            ->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->where('meta_keywords', 'like', "%{$tag}%");
        }

        $blogs = $query->latest()->paginate(6)->withQueryString();

        return  $this->view('frontend.blog', compact('blogs'));
    }
    public function blogDetails($slug)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);
        $blog = Cache::remember("blog_single_{$companyId}_{$slug}", $ttl, function () use ($slug, $companyId) {
            $slugWithDash = str_replace(['%20', ' '], '-', $slug);
            $slugWithSpace = str_replace(['%20', '-'], ' ', $slug);

            return Blog::with('user:id,name')
                ->where('company_id', $companyId)
                ->where(function ($query) use ($slugWithDash, $slugWithSpace, $slug) {
                    $query->where('slug', $slug)
                        ->orWhere('slug', $slugWithDash)
                        ->orWhere('slug', $slugWithSpace);
                })
                ->active()
                ->firstOrFail();
        });
        $relatedPostsPool = Cache::remember("blog_related_{$companyId}", $ttl, function () use ($companyId) {
            return Blog::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->latest()
                ->take(5) 
                ->get();
        });

        $relatedPosts = $relatedPostsPool
            ->where('id', '!=', $blog->id)
            ->take(3);

        $popularTags = Cache::remember("blog_tags_{$companyId}", $ttl, function () use ($companyId) {
            return  Blog::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->whereNotNull('meta_keywords')
                ->get()->pluck('meta_keywords')->flatten()->unique()->filter()->values();
        });
        return  $this->view('frontend.blogDetails', compact('blog', 'relatedPosts', 'popularTags'));
    }
}

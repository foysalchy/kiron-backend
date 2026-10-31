<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        if (request('preview_theme') && $blogs->isEmpty()) {
            $blogs = \App\Services\StorePreviewService::getDummyBlogs();
        }

        return  $this->view('frontend.blog', compact('blogs'));
    }
    public function blogDetails($slug)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        if (request('preview_theme')) {
            $dummyPost = \App\Services\StorePreviewService::getDummyBlogBySlug($slug);
            if ($dummyPost && str_starts_with($dummyPost->slug, $slug)) {
                $blog = $dummyPost;
                $relatedPosts = \App\Services\StorePreviewService::getDummyBlogs()->getCollection()->where('slug', '!=', $blog->slug)->take(3);
                $popularTags = collect($blog->meta_keywords ?? ['Lifestyle', 'Shopping', 'Trends']);
                return $this->view('frontend.blogDetails', compact('blog', 'relatedPosts', 'popularTags'));
            }
        }

        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request('preview_theme')) {
                $blog = \App\Services\StorePreviewService::getDummyBlogBySlug($slug);
                $relatedPosts = \App\Services\StorePreviewService::getDummyBlogs()->getCollection()->where('slug', '!=', $blog->slug)->take(3);
                $popularTags = collect($blog->meta_keywords ?? ['Lifestyle', 'Shopping', 'Trends']);
                return $this->view('frontend.blogDetails', compact('blog', 'relatedPosts', 'popularTags'));
            }
            throw $e;
        }
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


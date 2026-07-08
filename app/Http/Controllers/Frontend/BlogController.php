<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends FrontendController
{
    public function index(Request $request)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);
        $query = Blog::with('user')->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
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
        $slugWithDash = str_replace(['%20', ' '], '-', $slug);
        $slugWithSpace = str_replace(['%20', '-'], ' ', $slug);

        $blog = Blog::with('user')
            ->where(function ($query) use ($slugWithDash, $slugWithSpace, $slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug', $slugWithDash)
                    ->orWhere('slug', $slugWithSpace);
            })
            ->active()
            ->firstOrFail();

        $relatedPosts = Blog::active()
            ->where('id', '!=', $blog->id)
            ->latest()->take(3)->get();


        $popularTags = Blog::active()
            ->whereNotNull('meta_keywords')
            ->get()->pluck('meta_keywords')->flatten()->unique()->filter()->values();

        return  $this->view('frontend.blogDetails', compact('blog', 'relatedPosts', 'popularTags'));
    }
}

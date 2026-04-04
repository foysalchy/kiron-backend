<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $setup = getCurrentCompany();
        $template = $setup->template_name;

        $query = Blog::active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->whereJsonContains('meta_keywords', $tag);
        }

        $blogs = $query->latest()->paginate(6)->withQueryString();

        return view($template . '.frontend.blog', compact('setup', 'blogs'));
    }
}

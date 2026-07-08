<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AboutController extends FrontendController
{
    public function showPage($slug)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        $page = Cache::remember("page_content_{$companyId}_{$slug}", $ttl, function () use ($slug, $companyId) {
        return Page::where('slug', $slug)
            ->where('company_id', $companyId)
            ->where('status', Status::Active->value)
            ->select('id', 'company_id', 'title', 'slug', 'description','image', 'meta_title', 'meta_description', 'meta_keywords')
            ->firstOrFail();
        });
        return $this->view('frontend.page', compact('page'));
    }
}

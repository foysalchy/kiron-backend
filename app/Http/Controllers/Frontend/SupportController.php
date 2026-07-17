<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SupportController extends FrontendController
{
    public function index(Request $request)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);

        $query = KnowledgeBase::where('status', Status::Active);

        $query->when($request->search, function ($q) use ($request) {
            return $q->where(function ($sub) use ($request) {
                $sub->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        });

        $query->when($request->category, function ($q) use ($request) {
            return $q->where('category', $request->category);
        });

        $faqs = $query->get();

        $categories = Cache::remember("support_categories_{$companyId}", $ttl, function () use ($companyId) {
            return KnowledgeBase::where('company_id', $companyId)
                ->where('status', Status::Active)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category');
        });
        return $this->view('frontend.support', compact('faqs', 'categories'));
    }

}

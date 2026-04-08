<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $query = KnowledgeBase::where('company_id', $company->id)->active();

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

        $categories = KnowledgeBase::where('company_id', $company->id)
            ->active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view($template . '.frontend.support', compact('faqs', 'categories'));
    }
    public function storeMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $company = getCurrentCompany();

        ContactMessage::create([
            'company_id' => $company->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'is_read'    => 0,
        ]);

        return back()->with('success', 'আপনার প্রশ্নটি সফলভাবে পাঠানো হয়েছে।');
    }
}

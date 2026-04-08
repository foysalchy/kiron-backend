<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;

class ContctController extends Controller
{
    //
    public function index(Request $request)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $faqs = KnowledgeBase::where('company_id', $company->id)
                ->active()
                ->get();

        return view($template . '.frontend.contact',compact('faqs'));
    }
    public function send(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ], [
            'name.required'    => 'আপনার নাম লিখুন',
            'email.required'   => 'আপনার ইমেইল এড্রেস লিখুন',
            'phone.required'   => 'আপনার ফোন নম্বর লিখুন',
            'message.required' => 'আপনার বার্তাটি লিখুন',
        ]);

        $company = getCurrentCompany();
        $companyId = $company->company_id ?? $company->id;

        ContactMessage::create([
            'company_id' => $companyId,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'is_read'    => 0,
        ]);

        return back()->with('success', 'আপনার বার্তাটি আমাদের কাছে পৌঁছেছে। ধন্যবাদ!');
    }
}

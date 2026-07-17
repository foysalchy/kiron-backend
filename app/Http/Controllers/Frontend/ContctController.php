<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContctController extends FrontendController
{
    //
    public function index(Request $request)
    {
        $companyId = $this->company_id;
        $ttl = now()->addHours(6);
        $faqs = Cache::remember("home_faqs_{$companyId}", $ttl, function () use ($companyId) {
            return KnowledgeBase::where('company_id', $companyId)
                ->where('status', Status::Active->value)
                ->get();
        });
        return  $this->view('frontend.contact', compact('faqs'));
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
            'name.required'    => 'Please enter your name',
            'email.required'   => 'Please enter your email address',
            'phone.required'   => 'Please enter your phone number',
            'message.required' => 'Please write your message',
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

        return back()->with('success', 'Your message has been received. Thank you!');
    }
}

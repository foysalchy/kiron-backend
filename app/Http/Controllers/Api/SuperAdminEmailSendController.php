<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSuperAdminEmailSendRequest;
use App\Mail\SuperAdminEmailSendMail;
use App\Models\Company;
use App\Models\SuperAdminEmailSend;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class SuperAdminEmailSendController extends Controller
{
    /**
     * List all sent emails (paginated)
     */
    public function index(): JsonResponse
    {
        $emails = SuperAdminEmailSend::latest()->paginate(20);

        $emails->getCollection()->transform(function ($email) {
            $email->companies = $email->company_ids
                ? Company::whereIn('id', $email->company_ids)->get(['id', 'name'])
                : collect([]);

            return $email;
        });

        return response()->json([
            'success' => true,
            'data'    => $emails,
        ]);
    }

    /**
     * Send email to selected companies + custom emails
     */
    public function store(StoreSuperAdminEmailSendRequest $request): JsonResponse
    {
        $companyIds   = $request->input('company_ids', []);
        $customEmails = $request->input('custom_emails', []);
        $subject      = $request->input('subject');
        $body         = $request->input('body');

        // Collect all recipient email addresses
        $recipientEmails = collect();

        // 1. Company emails
        if (!empty($companyIds)) {
            $companyEmails = Company::whereIn('id', $companyIds)
                ->where('status', 1)          // only active companies
                ->whereNull('deleted_at')
                ->pluck('email');

            $recipientEmails = $recipientEmails->merge($companyEmails);
        }

        // 2. Custom emails
        if (!empty($customEmails)) {
            $recipientEmails = $recipientEmails->merge($customEmails);
        }

        // Deduplicate
        $recipientEmails = $recipientEmails->unique()->values();

        if ($recipientEmails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipients found.',
            ], 422);
        }

        // Send emails
        $mailable = new SuperAdminEmailSendMail($subject, $body);

        foreach ($recipientEmails as $email) {
            Mail::to($email)->queue($mailable);
        }

        // Save record
        SuperAdminEmailSend::create([
            'subject'          => $subject,
            'body'             => $body,
            'company_ids'      => $companyIds,
            'custom_emails'    => $customEmails,
            'total_recipients' => $recipientEmails->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Email queued for {$recipientEmails->count()} recipient(s) successfully.",
        ]);
    }

    public function show($id): JsonResponse
    {
        $email = SuperAdminEmailSend::findOrFail($id);

        $companies = $email->company_ids
            ? Company::whereIn('id', $email->company_ids)->get(['id', 'name'])
            : collect([]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $email->id,
                'subject'          => $email->subject,
                'body'             => $email->body,   
                'companies'        => $companies,
                'custom_emails'    => $email->custom_emails ?? [],
                'total_recipients' => $email->total_recipients,
                'created_at'       => $email->created_at,
            ],
        ]);
    }
}

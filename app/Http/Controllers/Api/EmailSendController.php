<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Email\EmailSendRequest;
use App\Services\EmailSendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailSendController extends Controller
{
    public function __construct(protected EmailSendService $service)
    {}

    /**
     * Display a listing of sent emails.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getAllEmailLogs($request->all());
        return ResponseHelper::success($data, 'Email logs retrieved successfully');
    }

    /**
     * Store and Send Email.
     */
    public function store(EmailSendRequest $request): JsonResponse
    {
        $data = $this->service->createEmailSend($request->validated());
        return ResponseHelper::success($data, 'Emails are being processed/sent', 201);
    }

    /**
     * Display the specified email log.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->service->getEmailLogById($id);
        return ResponseHelper::success($data, 'Email details retrieved');
    }
}

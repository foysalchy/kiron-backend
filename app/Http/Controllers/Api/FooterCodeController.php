<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFooterCodeRequest;
use App\Services\FooterCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FooterCodeController extends Controller
{
    public function __construct(protected FooterCodeService $footerCodeService)
    {
    }

    /**
     * Display the footer code for the current company.
     */
    public function index(): JsonResponse
    {
        $data = $this->footerCodeService->getFooterCode();

        return ResponseHelper::success($data, 'Footer code retrieved successfully');
    }

    /**
     * Store or Update the footer code.
     */
    public function store(UpdateFooterCodeRequest $request): JsonResponse
    {
        $data = $this->footerCodeService->saveFooterCode($request->validated());

        return ResponseHelper::success($data, 'Footer code saved successfully');
    }
}

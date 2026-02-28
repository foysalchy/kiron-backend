<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreRecurringJournalRequest;
use App\Services\RecurringJournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringJournalController extends Controller
{
    public function __construct(protected RecurringJournalService $recurringJournal){}

    /**
     * Store a newly created recurring journal with details.
     */
    public function store(StoreRecurringJournalRequest $request): JsonResponse
    {
        $data = $this->recurringJournal->createJournal($request->validated());

        return ResponseHelper::success($data, 'Transfer created successfully', 201);
    }

    /**
     * Display the specified recurring journal.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->recurringJournal->getJournalById($id);

        return ResponseHelper::success($data, 'Transfer details retrieved successfully');
    }


}

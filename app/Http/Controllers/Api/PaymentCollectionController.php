<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\PartyDueService;
use App\Services\PaymentCollectionService;
use Illuminate\Http\Request;

class PaymentCollectionController extends Controller
{

    public function __construct(
        protected PaymentCollectionService $paymentCollectionService,
        protected PartyDueService $partyDueService,
    ) {}
    public function paymentCollection(Request $request)
    {
        $filters = $request->only(['direction', 'party_id', 'search', 'per_page']);

        $data = [
            'list'    => $this->paymentCollectionService->getList($filters),
            'summary' => $this->paymentCollectionService->getSummary($filters),
        ];

        return ResponseHelper::success($data, 'Payment collection list retrieved');
    }
    public function transactions(Request $request, PartyDueService $service)
    {
        $validated = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'search'    => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $service->getPaymentTransactions(
            $validated['direction'],
            $validated['search'] ?? null,
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null,
            $validated['per_page'] ?? 20
        );

        return response()->json(['data' => $result]);
    }
    public function getDueParties(Request $request)
    {
        $direction = $request->query('direction', 'in'); // 'in' | 'out'
        $search    = $request->query('search');
        $perPage   = (int) $request->query('per_page', 20);

        $data = $this->partyDueService->getPartyDueList($direction, $search, $perPage);

        return ResponseHelper::success($data, 'Due party list retrieved');
    }

    public function getPartyDueInvoices(Request $request, int $partyId)
    {
        $direction = $request->query('direction', 'in');

        $data = $this->partyDueService->getPartyDueInvoices($partyId, $direction);

        return ResponseHelper::success($data, 'Party due invoices retrieved');
    }
    public function settlePayments(Request $request, int $partyId)
    {
        $validated = $request->validate([
            'direction'              => 'required|in:in,out',
            'amount'                 => 'required|numeric|min:0.01',
            'payment_date'           => 'required|date',
            'payment_mode'           => 'required|string',
            'note'                   => 'nullable|string',
            'allocations'            => 'required|array|min:1',
            'allocations.*.ref_id'   => 'required|integer',
            'allocations.*.amount'   => 'required|numeric|min:0',
        ]);

        $result = $this->partyDueService->settlePayments(
            $partyId,
            $validated['direction'],
            (float) $validated['amount'],
            $validated['payment_date'],
            $validated['payment_mode'],
            $validated['note'] ?? null,
            $validated['allocations']
        );

        return ResponseHelper::success($result, 'Payment settled successfully');
    }
}

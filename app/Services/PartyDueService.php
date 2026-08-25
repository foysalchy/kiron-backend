<?php

namespace App\Services;

use App\Models\Party;
use App\Models\Order;
use App\Models\Purchase;
use App\Enums\Status;
use App\Enums\PurchaseStatus;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\OrderPayment;
use App\Models\OrderReturn;
use App\Models\PaymentTransaction;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartyDueService
{
    /**
     * Recalculate and update a party's due_amount based on their type.
     * Customer -> receivable from orders.
     * Supplier -> payable from purchases.
     * Single source of truth: orders/purchases table.
     *
     * @param int|null $partyId
     * @return void
     */
    public function recalculatePartyDue(?int $partyId): void
    {

        if (!$partyId) {
            return;
        }

        $party = Party::find($partyId);
        if (!$party) {
            return;
        }

        $totalDue = match ($party->type) {
            Party::TYPE_CUSTOMER => $this->calculateCustomerDue($partyId),
            Party::TYPE_SUPPLIER => $this->calculateSupplierDue($partyId),
            default => null,
        };
        Log::info($totalDue);
        if ($totalDue === null) {
            return; // unknown type, nothing to calculate
        }

        Party::where('id', $partyId)->update(['due_amount' => $totalDue]);
    }

    private function calculateCustomerDue(int $customerId): float
    {
        return (float) Order::query()
            ->leftJoinSub(
                OrderReturn::query()
                    ->where('order_returns.status', '!=', Status::Cancelled->value)
                    ->join('order_return_payments', 'order_return_payments.order_return_id', '=', 'order_returns.id')
                    ->select('order_returns.order_id', DB::raw('SUM(order_return_payments.amount) as returned_amount'))
                    ->groupBy('order_returns.order_id'),
                'ret',
                'ret.order_id',
                '=',
                'orders.id'
            )
            ->where('orders.customer_id', $customerId)
            ->where('orders.status', '!=', Status::Cancelled->value)
            ->selectRaw('COALESCE(SUM(GREATEST(orders.grand_total - orders.payment_amount - COALESCE(ret.returned_amount, 0), 0)), 0) as total')
            ->value('total');
    }


private function calculateSupplierDue(int $supplierId): float
{
    return (float) Purchase::query()
        ->leftJoinSub(
            DB::table('purchase_payments')
                ->select('purchase_id', DB::raw('SUM(amount) as paid_amount'))
                ->whereNull('deleted_at')
                ->groupBy('purchase_id'),
            'pp',
            'pp.purchase_id',
            '=',
            'purchases.id'
        )
        ->leftJoinSub(
            PurchaseReturn::query()
                ->where('purchase_returns.status', '!=', Status::Cancelled->value)
                ->join('purchase_payment_returns', 'purchase_payment_returns.purchase_return_id', '=', 'purchase_returns.id')
                ->select('purchase_returns.purchase_id', DB::raw('SUM(purchase_payment_returns.amount) as returned_amount'))
                ->groupBy('purchase_returns.purchase_id'),
            'ret',
            'ret.purchase_id',
            '=',
            'purchases.id'
        )
        ->where('purchases.supplier_id', $supplierId)
        ->where('purchases.status', '!=', Status::Cancelled->value)
        ->selectRaw('COALESCE(SUM(GREATEST(purchases.grand_total - COALESCE(pp.paid_amount, 0) - COALESCE(ret.returned_amount, 0), 0)), 0) as total')
        ->value('total');
}
    public function getPartyDueList(string $direction, ?string $search, int $perPage)
    {
        if ($direction === 'in') {
            $rowDueSub = Order::query()
                ->leftJoinSub(
                    OrderReturn::query()
                        ->where('status', '!=',  Status::Cancelled->value)
                        ->select('order_id', DB::raw('SUM(refund_amount) as returned_amount'))
                        ->groupBy('order_id'),
                    'ret',
                    'ret.order_id',
                    '=',
                    'orders.id'
                )
                ->where('orders.status', '!=',  Status::Cancelled->value)
                ->whereNotNull('orders.customer_id')
                ->selectRaw('orders.customer_id as party_id, GREATEST(orders.grand_total - orders.payment_amount - COALESCE(ret.returned_amount, 0), 0) as row_due');
        } else {
            $rowDueSub = Purchase::query()
                ->leftJoinSub(
                    DB::table('purchase_payments')
                        ->select('purchase_id', DB::raw('SUM(amount) as paid_amount'))
                        ->whereNull('deleted_at')
                        ->groupBy('purchase_id'),
                    'pp',
                    'pp.purchase_id',
                    '=',
                    'purchases.id'
                )
                ->leftJoinSub(
                    PurchaseReturn::query()
                        ->where('status', '!=',  Status::Cancelled->value)
                        ->select('purchase_id', DB::raw('SUM(refund_amount) as returned_amount'))
                        ->groupBy('purchase_id'),
                    'ret',
                    'ret.purchase_id',
                    '=',
                    'purchases.id'
                )
                ->where('purchases.status', '!=',  Status::Cancelled->value)
                ->whereNotNull('purchases.supplier_id')
                ->selectRaw('purchases.supplier_id as party_id, GREATEST(purchases.grand_total - COALESCE(pp.paid_amount, 0) - COALESCE(ret.returned_amount, 0), 0) as row_due');
        }

        $query = Party::query()
            ->joinSub($rowDueSub, 'd', 'd.party_id', '=', 'parties.id')
            ->groupBy('parties.id', 'parties.name', 'parties.phone', 'parties.balance')
            ->havingRaw('SUM(d.row_due) > 0')
            ->selectRaw('parties.id as party_id, parties.name as party_name, parties.phone as party_phone, parties.balance as balance, SUM(d.row_due) as total_due, COUNT(*) as invoice_count');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('parties.name', 'like', "%{$search}%")
                    ->orWhere('parties.phone', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc('total_due');

        return $query->paginate($perPage);
    }
    public function getPaymentTransactions(string $direction, ?string $search, ?string $dateFrom, ?string $dateTo, int $perPage)
    {
        $query = PaymentTransaction::query()
            ->with('party:id,name,phone')
            ->where('direction', $direction);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                    ->orWhereHas('party', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $query->orderByDesc('payment_date')->orderByDesc('id');

        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(fn($tx) => [
            'id'             => $tx->id,
            'transaction_no' => $tx->transaction_no,
            'amount'         => $tx->amount,
            'payment_date'   => $tx->payment_date,
            'payment_mode'   => $tx->payment_mode,
            'reference_no'   => $tx->reference_no,
            'note'           => $tx->note,
            'party_name'     => $tx->party?->name,
            'party_phone'    => $tx->party?->phone,
        ]);

        return $paginated;
    }
    /**
     * Due invoice list for a single party — used in the settle modal.
     */
    public function getPartyDueInvoices(int $partyId, string $direction)
    {
        if ($direction === 'in') {
            return Order::query()
                ->leftJoinSub(
                    OrderReturn::query()
                        ->where('status', '!=',  Status::Cancelled->value)
                        ->select('order_id', DB::raw('SUM(grand_total) as returned_amount'))
                        ->groupBy('order_id'),
                    'ret',
                    'ret.order_id',
                    '=',
                    'orders.id'
                )
                ->where('orders.customer_id', $partyId)
                ->where('orders.status', '!=',  Status::Cancelled->value)
                ->selectRaw('orders.id, orders.order_no as invoice_no, orders.order_date as invoice_date, orders.grand_total, GREATEST(orders.grand_total - orders.payment_amount - COALESCE(ret.returned_amount, 0), 0) as due_amount')
                ->havingRaw('due_amount > 0')
                ->orderBy('orders.order_date')
                ->get();
        }

        return Purchase::query()
            ->leftJoinSub(
                DB::table('purchase_payments')
                    ->select('purchase_id', DB::raw('SUM(amount) as paid_amount'))
                    ->whereNull('deleted_at')
                    ->groupBy('purchase_id'),
                'pp',
                'pp.purchase_id',
                '=',
                'purchases.id'
            )
            ->leftJoinSub(
                PurchaseReturn::query()
                    ->where('status', '!=',  Status::Cancelled->value)
                    ->select('purchase_id', DB::raw('SUM(grand_total) as returned_amount'))
                    ->groupBy('purchase_id'),
                'ret',
                'ret.purchase_id',
                '=',
                'purchases.id'
            )
            ->where('purchases.supplier_id', $partyId)
            ->where('purchases.status', '!=',  Status::Cancelled->value)
            ->selectRaw('purchases.id, purchases.reference_no as invoice_no, purchases.purchase_date as invoice_date, purchases.grand_total, GREATEST(purchases.grand_total - COALESCE(pp.paid_amount, 0) - COALESCE(ret.returned_amount, 0), 0) as due_amount')
            ->havingRaw('due_amount > 0')
            ->orderBy('purchases.purchase_date')
            ->get();
    }

    public function settlePayments(
        int $partyId,
        string $direction,
        float $totalAmount,
        string $paymentDate,
        string $paymentMode,
        ?string $note,
        array $allocations
    ): array {
        DB::beginTransaction();

        try {
            if ($totalAmount <= 0) {
                throw ApiException::badRequest('Payment amount must be greater than zero');
            }

            $party = Party::where('id', $partyId)->lockForUpdate()->first();
            if (!$party) {
                throw ApiException::notFound('Party not found');
            }

            // Filter out zero/empty allocations
            $allocations = array_values(array_filter($allocations, fn($a) => (float) ($a['amount'] ?? 0) > 0));

            $allocatedTotal = round(array_sum(array_column($allocations, 'amount')), 2);
            $totalAmount = round($totalAmount, 2);

            if ($allocatedTotal > $totalAmount) {
                throw ApiException::badRequest('Allocated amount cannot exceed the total payment amount');
            }
            if ($direction === 'in') {
                $this->settleOrderAllocations($partyId, $allocations, $paymentDate, $paymentMode, $note);
            } elseif ($direction === 'out') {
                $this->settlePurchaseAllocations($partyId, $allocations, $paymentDate, $paymentMode, $note);
            } else {
                throw ApiException::badRequest('Invalid direction');
            }

            // Unused portion becomes wallet credit
            // $unusedAmount = $totalAmount - $allocatedTotal;
            // if ($unusedAmount > 0) {
            //     $party->increment('balance', $unusedAmount);
            // }
            PaymentTransaction::create([
                'company_id'     => $party->company_id,
                'party_id'       => $partyId,
                'direction'      => $direction,
                'transaction_no' => $this->generateTransactionNo($direction, $party->company_id),
                'amount'         => $totalAmount,
                'payment_date'   => $paymentDate,
                'payment_mode'   => $paymentMode,
                'reference_no'   => $note['reference_no'] ?? null,
                'note'           => $note,
                'created_by'     => auth()->id(),
            ]);
            $this->recalculatePartyDue($partyId);

            LogHelper::custom(
                'payment_collection',
                'parties',
                $partyId,
                $party->company_id,
                "Collected {$totalAmount}, allocated {$allocatedTotal}"
            );

            DB::commit();

            return [
                'party_id'        => $partyId,
                'total_amount'    => $totalAmount,
                'allocated_total' => $allocatedTotal,

                'new_balance'     => $party->fresh()->balance,
                'new_due_amount'  => $party->fresh()->due_amount,
            ];
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment settlement failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to settle payment');
        }
    }
    private function generateTransactionNo(string $direction, int $companyId): string
    {
        $prefix = $direction === 'in' ? 'PAY-IN' : 'PAY-OUT';
        $datePart = now()->format('ymd');

        // Lock the last transaction of this company+direction+day to avoid duplicate numbers
        // under concurrent requests.
        $last = PaymentTransaction::where('company_id', $companyId)
            ->where('direction', $direction)
            ->where('transaction_no', 'like', "{$prefix}-{$datePart}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextSeq = 1;
        if ($last) {
            $lastSeq = (int) substr($last->transaction_no, -4);
            $nextSeq = $lastSeq + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $datePart, $nextSeq);
    }
    private function settleOrderAllocations(int $customerId, array $allocations, string $paymentDate, string $paymentMode, ?string $note): void
    {
        foreach ($allocations as $alloc) {
            $order = Order::where('id', $alloc['ref_id'])
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw ApiException::notFound("Order #{$alloc['ref_id']} not found for this customer");
            }

            if ($order->status === Status::Cancelled->value) {
                throw ApiException::badRequest("Order #{$order->order_no} is cancelled, cannot settle");
            }

            $orderDue = max(0, $order->grand_total - $order->payment_amount);
            $amount = (float) $alloc['amount'];

            if ($amount > $orderDue) {
                throw ApiException::badRequest("Amount exceeds due for order #{$order->order_no}");
            }

            OrderPayment::create([
                'order_id'       => $order->id,
                'amount'         => $amount,
                'change_amount'  => 0,
                'payment_method' => $paymentMode,
                'reference_no'   => null,
                'note'           => $note,
            ]);

            $newPaid = $order->payment_amount + $amount;
            $order->update([
                'payment_amount' => $newPaid,
                'payment_status' => $this->determinePaymentStatus($order->grand_total, $newPaid),
            ]);
        }
    }
    private function determinePaymentStatus(float $grandTotal, float $paidAmount): int
    {
        if ($paidAmount <= 0) {
            return Order::PAYMENT_UNPAID;
        }

        if ($paidAmount >= $grandTotal) {
            return Order::PAYMENT_PAID;
        }

        return Order::PAYMENT_PARTIAL;
    }

    private function settlePurchaseAllocations(int $supplierId, array $allocations, string $paymentDate, string $paymentMode, ?string $note): void
    {
        foreach ($allocations as $alloc) {
            $purchase = Purchase::where('id', $alloc['ref_id'])
                ->where('supplier_id', $supplierId)
                ->lockForUpdate()
                ->first();

            if (!$purchase) {
                throw ApiException::notFound("Purchase #{$alloc['ref_id']} not found for this supplier");
            }

            if ($purchase->status === Status::Cancelled->value) {
                throw ApiException::badRequest("Purchase #{$purchase->reference_no} is cancelled, cannot settle");
            }

            $paidSoFar = PurchasePayment::where('purchase_id', $purchase->id)->sum('amount');
            $purchaseDue = max(0, $purchase->grand_total - $paidSoFar);
            $amount = (float) $alloc['amount'];

            if ($amount > $purchaseDue) {
                throw ApiException::badRequest("Amount exceeds due for purchase #{$purchase->reference_no}");
            }

            PurchasePayment::create([
                'purchase_id'   => $purchase->id,
                'amount'        => $amount,
                'payment_date'  => $paymentDate,
                'payment_type'  => $paymentMode,
                'account'       => null,
                'reference_no'  => null,
                'note'          => $note,
            ]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaymentCollectionService
{
    public function getList(array $filters = []): LengthAwarePaginator
    {
        $direction = $filters['direction'] ?? null; // 'receivable' | 'payable' | null
        $partyId   = $filters['party_id'] ?? null;
        $search    = $filters['search'] ?? null;
        $perPage   = (int) ($filters['per_page'] ?? 20);

        $ordersQuery = Order::query()
            ->join('parties as p', 'p.id', '=', 'orders.customer_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereRaw('(orders.grand_total - orders.payment_amount) > 0')
            ->select([
                DB::raw("'receivable' as direction"),
                DB::raw("'customer' as party_type"),
                'p.id as party_id',
                'p.name as party_name',
                'p.phone as party_phone',
                'orders.id as ref_id',
                'orders.order_no as reference_no',
                'orders.order_date as ref_date',
                'orders.grand_total as grand_total',
                'orders.payment_amount as payment_amount',
                DB::raw('GREATEST(orders.grand_total - orders.payment_amount, 0) as due_amount'),
                'orders.status as status',
            ]);

        $purchasesQuery = Purchase::query()
            ->join('parties as p', 'p.id', '=', 'purchases.supplier_id')
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
            ->where('purchases.status', '!=', 'cancelled')
            ->whereRaw('(purchases.grand_total - COALESCE(pp.paid_amount, 0)) > 0')
            ->select([
                DB::raw("'payable' as direction"),
                DB::raw("'supplier' as party_type"),
                'p.id as party_id',
                'p.name as party_name',
                'p.phone as party_phone',
                'purchases.id as ref_id',
                'purchases.reference_no as reference_no',
                'purchases.purchase_date as ref_date',
                'purchases.grand_total as grand_total',
                DB::raw('COALESCE(pp.paid_amount, 0) as payment_amount'),
                DB::raw('GREATEST(purchases.grand_total - COALESCE(pp.paid_amount, 0), 0) as due_amount'),
                'purchases.status as status',
            ]);
        // Apply party filter BEFORE union — filters within each Eloquent builder,
        // so Global Scope + this condition combine correctly per model.
        if ($partyId) {
            $ordersQuery->where('p.id', $partyId);
            $purchasesQuery->where('p.id', $partyId);
        }

        if ($search) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('orders.order_no', 'like', "%{$search}%")
                    ->orWhere('p.phone', 'like', "%{$search}%");
            });
            $purchasesQuery->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('purchases.reference_no', 'like', "%{$search}%")
                    ->orWhere('p.phone', 'like', "%{$search}%");
            });
        }

        // Pick which side(s) to include based on direction filter
        if ($direction === 'receivable') {
            $combined = $ordersQuery;
        } elseif ($direction === 'payable') {
            $combined = $purchasesQuery;
        } else {
            // union() on Eloquent builder — Global Scopes already baked into
            // each query's SQL at this point, so union is safe.
            $combined = $ordersQuery->unionAll($purchasesQuery);
        }

        // Wrap the (possibly unioned) query for ordering + manual pagination,
        // since union queries can't always .orderBy() a raw-select column directly
        // depending on DB driver — safest is to order in the outer wrapper.
        $sql = $combined->toSql();
        $bindings = $combined->getBindings();

        $countSql = "select count(*) as aggregate from ({$sql}) as pc";
        $total = DB::select($countSql, $bindings)[0]->aggregate ?? 0;

        $page = Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $rowsSql = "select * from ({$sql}) as pc order by ref_date desc limit {$perPage} offset {$offset}";
        $rows = DB::select($rowsSql, $bindings);

        return new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

  public function getSummary(array $filters = []): array
{
    $partyId = $filters['party_id'] ?? null;

    $orderQuery = Order::query()->where('status', '!=', 'cancelled');

    if ($partyId) {
        $orderQuery->where('customer_id', $partyId);
    }

    $totalReceivable = (float) $orderQuery
        ->selectRaw('COALESCE(SUM(GREATEST(grand_total - payment_amount, 0)), 0) as total')
        ->value('total');

    $purchaseQuery = Purchase::query()
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
        ->where('purchases.status', '!=', 'cancelled');

    if ($partyId) {
        $purchaseQuery->where('purchases.supplier_id', $partyId);
    }

    $totalPayable = (float) $purchaseQuery
        ->selectRaw('COALESCE(SUM(GREATEST(purchases.grand_total - COALESCE(pp.paid_amount, 0), 0)), 0) as total')
        ->value('total');

    return [
        'total_receivable' => $totalReceivable,
        'total_payable'    => $totalPayable,
    ];
}
}

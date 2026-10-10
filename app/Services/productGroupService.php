<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Log, DB};

class productGroupService
{


    public function getAllGroups(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductGroup::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching customer groups: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch customer groups');
        }
    }

    public function createGroup(array $data): ProductGroup
    {
        DB::beginTransaction();
        try {

            $group = ProductGroup::create($data);
            LogHelper::created('customer_group', $group->id, $group->company_id, $group->name);

            DB::commit();
            return $group;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Group creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create customer group');
        }
    }

    public function updateGroup(int $id, array $data): ProductGroup
    {
        DB::beginTransaction();
        try {
            $group = ProductGroup::findOrFail($id);
            $group->update($data);
            LogHelper::updated('customer_group', $group->id, $group->company_id, $group->name);

            DB::commit();
            return $group;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Group update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update customer group');
        }
    }
    public function createGroup2(array $data): ProductGroup
    {
        DB::beginTransaction();
        try {

            $group = ProductGroup::create($data);
            LogHelper::created('customer_group', $group->id, $group->company_id, $group->name);

            DB::commit();
            return $group;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Group creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create customer group');
        }
    }


    /**
     * Remove a single customer from the group
     */
    public function removeCustomerFromGroup(int $groupId, int $customerId): ProductGroup
    {
        $group = ProductGroup::findOrFail($groupId);


        $currentIds = $group->customer_ids ?? [];

        // Filter out the customerId and re-index the array
        $newIds = array_values(array_filter($currentIds, fn($id) => $id != $customerId));

        $group->update(['customer_ids' => $newIds]);

        LogHelper::updated('customer_group', $group->id, $group->company_id, 'Removed customer ' . $customerId . ' from group ' . $group->name);

        return $group;
    }
    public function toggleStatus(int $id): ProductGroup
    {
        try {
            $group = ProductGroup::findOrFail($id);
            $currentStatus = Status::from($group->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $group->update(['status' => $newStatus->value]);
            LogHelper::statusChanged('customer_group', $group->id, $group->company_id, $group->name . ' new status ' . $newStatus->label());

            return $group;
        } catch (\Exception $e) {
            Log::error('Customer group status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
    public function toggleFrontend(int $id): ProductGroup
    {
        try {
            $group = ProductGroup::findOrFail($id);
            $group->update(['is_frontend' => !$group->is_frontend]);
            LogHelper::statusChanged('customer_group', $group->id, $group->company_id, $group->name . 'status update ');

            return $group;
        } catch (\Exception $e) {
            Log::error('Customer group status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function deleteGroup(int $id): bool
    {
        $group = ProductGroup::findOrFail($id);
        $group->delete();
        LogHelper::deleted('product_group', $group->id, $group->company_id, $group->name);
        return true;
    }

    public function restoreGroup(int $id): bool
    {
        $group = ProductGroup::withTrashed()->findOrFail($id);
        $group->restore();
        LogHelper::updated('product_group', $group->id, $group->company_id, 'Restored ' . $group->name);
        return true;
    }

    public function forceDeleteGroup(int $id): bool
    {
        $group = ProductGroup::withTrashed()->findOrFail($id);
        $group->forceDelete();
        LogHelper::deleted('product_group', $group->id, $group->company_id, 'Permanently deleted ' . $group->name);
        return true;
    }

    /**
     * Get Group with Hydrated Customers
     */
    public function getGroupWithProducts(int $id): array
    {
        $group = ProductGroup::findOrFail($id);

        $products = Product::whereIn('id', $group->product_ids ?? [])
            ->select('id', 'title', 'thumbnail_95')
            ->get();

        $params = $group->filter_parameters ?? [];
        $filterDetails = [];

        if ($group->filter_type === 'most_sold_quantity') {
            if (!empty($params['date_from']))    $filterDetails['Date From']    = $params['date_from'];
            if (!empty($params['date_to']))      $filterDetails['Date To']      = $params['date_to'];
            if (!empty($params['min_quantity'])) $filterDetails['Min Qty Sold'] = $params['min_quantity'];
            if (!empty($params['limit']))        $filterDetails['Limit']        = $params['limit'];
        }
        if ($group->filter_type === 'no_sales_products') {
            $filterDetails['Period'] = 'All Time';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
            if (!empty($params['date_from']) || !empty($params['date_to'])) {
                unset($filterDetails['Period']);
            }
        }
        if ($group->filter_type === 'low_selling_products') {
            $filterDetails['Max Qty Threshold'] = ($params['threshold'] ?? 2) . ' units';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }

        if ($group->filter_type === 'frequently_reordered') {
            $filterDetails['Min Order Count'] = ($params['min_reorder_count'] ?? 2) . ' orders';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }
        if ($group->filter_type === 'high_profit_products') {
            if (!empty($params['date_from']))  $filterDetails['Date From']  = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']    = $params['date_to'];
            if (!empty($params['min_profit_amount'])) $filterDetails['Min Profit Amount'] = $params['min_profit_amount'];
            if (!empty($params['limit']))      $filterDetails['Limit']      = $params['limit'];
        }

        if ($group->filter_type === 'high_profit_margin_products') {
            if (!empty($params['date_from']))  $filterDetails['Date From']  = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']    = $params['date_to'];
            if (!empty($params['min_margin'])) $filterDetails['Min Margin'] = $params['min_margin'] . '%';
            if (!empty($params['limit']))      $filterDetails['Limit']      = $params['limit'];
        }

        if ($group->filter_type === 'low_profit_margin_products') {
            if (!empty($params['date_from']))  $filterDetails['Date From']  = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']    = $params['date_to'];
            if (!empty($params['max_margin'])) $filterDetails['Max Margin'] = $params['max_margin'] . '%';
            if (!empty($params['limit']))      $filterDetails['Limit']      = $params['limit'];
        }
        if ($group->filter_type === 'high_discount_products') {
            if (!empty($params['min_discount'])) $filterDetails['Min Discount'] = $params['min_discount'] . '%';
            if (!empty($params['limit']))        $filterDetails['Limit']        = $params['limit'];
        }

        if ($group->filter_type === 'full_price_sold_products') {
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }
        if ($group->filter_type === 'most_wishlisted') {
            if (!empty($params['date_from']))  $filterDetails['Date From']        = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']          = $params['date_to'];
            if (!empty($params['min_count']))  $filterDetails['Min Wishlist Count'] = $params['min_count'];
            if (!empty($params['limit']))      $filterDetails['Limit']            = $params['limit'];
        }

        if ($group->filter_type === 'frequently_viewed') {
            if (!empty($params['date_from']))  $filterDetails['Date From']      = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']        = $params['date_to'];
            if (!empty($params['min_count']))  $filterDetails['Min View Count'] = $params['min_count'];
            if (!empty($params['limit']))      $filterDetails['Limit']          = $params['limit'];
        }
        if ($group->filter_type === 'high_view_low_order') {
            $filterDetails['Max Conversion Rate'] = ($params['conversion_rate'] ?? 5) . '%';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }

        if ($group->filter_type === 'high_wishlist_low_order') {
            $filterDetails['Max Conversion Rate'] = ($params['conversion_rate'] ?? 5) . '%';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }
        if ($group->filter_type === 'most_added_to_cart') {
            if (!empty($params['date_from']))  $filterDetails['Date From']     = $params['date_from'];
            if (!empty($params['date_to']))    $filterDetails['Date To']       = $params['date_to'];
            if (!empty($params['min_count']))  $filterDetails['Min Cart Count'] = $params['min_count'];
            if (!empty($params['limit']))      $filterDetails['Limit']         = $params['limit'];
        }
        if ($group->filter_type === 'high_cart_low_purchase') {
            $filterDetails['Max Conversion Rate'] = ($params['conversion_rate'] ?? 5) . '%';
            if (!empty($params['date_from'])) $filterDetails['Date From'] = $params['date_from'];
            if (!empty($params['date_to']))   $filterDetails['Date To']   = $params['date_to'];
            if (!empty($params['limit']))     $filterDetails['Limit']     = $params['limit'];
        }
        if ($group->filter_type === 'trending_products') {
            $filterDetails['Min Trending Score'] = ($params['min_trending_score'] ?? 20);
            $filterDetails['Period']             = 'Last 7 days vs Previous 7 days';
            if (!empty($params['limit']))        $filterDetails['Limit'] = $params['limit'];
        }
        return [
            'group'          => $group,
            'products'       => $products,
            'filter_details' => $filterDetails,
        ];
    }
    public function getProductsByCriteria(string $type, array $params = []): Collection
    {
        try {
            if ($type === 'most_sold_quantity') {
                $query = OrderDetail::query()
                    ->select('product_id', DB::raw('SUM(quantity) as total_qty_sold'))
                    ->whereHas('order', function ($q) use ($params) {

                        $q->where('status', '!=', Status::Cancelled->value);

                        if (!empty($params['date_from'])) {
                            $q->whereDate('order_date', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('order_date', '<=', $params['date_to']);
                        }
                    })
                    ->with(['product' => function ($q) {
                        $q->select('id', 'title', 'thumbnail_95');
                    }])
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty_sold');

                if (!empty($params['min_quantity'])) {
                    $query->having('total_qty_sold', '>=', (int) $params['min_quantity']);
                }

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get();
            }
            if ($type === 'no_sales_products') {
                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95');

                if (!empty($params['date_from']) || !empty($params['date_to'])) {
                    $query->whereDoesntHave('orderDetails.order', function ($q) use ($params) {
                        $q->where('status', '!=', Status::Cancelled->value);
                        if (!empty($params['date_from'])) {
                            $q->whereDate('order_date', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('order_date', '<=', $params['date_to']);
                        }
                    });
                } else {
                    $query->whereDoesntHave('orderDetails');
                }

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                $query->orderBy('created_at', 'desc');

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'     => $product->id,
                        'total_qty_sold' => 0,
                        'product'        => $product,
                    ];
                });
            }
            if ($type === 'low_selling_products') {
                $threshold = !empty($params['threshold']) ? (int) $params['threshold'] : 2;

                $orderConditions = function ($q) use ($params) {
                    $q->where('status', '!=', Status::Cancelled->value);
                    if (!empty($params['date_from'])) {
                        $q->whereDate('order_date', '>=', $params['date_from']);
                    }
                    if (!empty($params['date_to'])) {
                        $q->whereDate('order_date', '<=', $params['date_to']);
                    }
                };

                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95')
                    ->withCount(['orderDetails as total_qty_sold' => function ($q) use ($orderConditions) {
                        $q->whereHas('order', $orderConditions);
                    }])
                    ->having('total_qty_sold', '>', 0)
                    ->having('total_qty_sold', '<=', $threshold)
                    ->orderBy('total_qty_sold', 'asc');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'     => $product->id,
                        'total_qty_sold' => $product->total_qty_sold,
                        'product'        => $product,
                    ];
                });
            }

            if ($type === 'frequently_reordered') {
                $threshold = !empty($params['min_reorder_count']) ? (int) $params['min_reorder_count'] : 2;

                $orderConditions = function ($q) use ($params) {
                    $q->where('status', '!=', Status::Cancelled->value);
                    if (!empty($params['date_from'])) {
                        $q->whereDate('order_date', '>=', $params['date_from']);
                    }
                    if (!empty($params['date_to'])) {
                        $q->whereDate('order_date', '<=', $params['date_to']);
                    }
                };

                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95')
                    ->withCount(['orderDetails as total_order_count' => function ($q) use ($orderConditions) {
                        $q->whereHas('order', $orderConditions);
                    }])
                    ->having('total_order_count', '>=', $threshold)
                    ->orderByDesc('total_order_count');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'        => $product->id,
                        'total_qty_sold'    => $product->total_order_count,
                        'total_order_count' => $product->total_order_count,
                        'product'           => $product,
                    ];
                });
            }
            if ($type === 'trending_products') {
                $minScore = !empty($params['min_trending_score']) ? (float) $params['min_trending_score'] : 20.0;

                $now            = now();
                $current_start  = $now->copy()->subDays(7)->format('Y-m-d');
                $current_end    = $now->format('Y-m-d');
                $previous_start = $now->copy()->subDays(14)->format('Y-m-d');
                $previous_end   = $now->copy()->subDays(8)->format('Y-m-d');

                $cancelledStatus = Status::Cancelled->value;

                $query = Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.thumbnail_95',
                        'products.regular_price',
                        'products.stock_quantity',
                        'products.stock_status',

                        // Current Orders
                        DB::raw("(
                SELECT COUNT(od.id)
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                    AND o.type = 'sales'
                WHERE od.product_id = products.id
                AND o.status != $cancelledStatus
                AND o.deleted_at IS NULL
                AND DATE(o.order_date) BETWEEN '$current_start' AND '$current_end'
            ) as current_orders"),

                        // Previous Orders
                        DB::raw("(
                SELECT COUNT(od.id)
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                    AND o.type = 'sales'
                WHERE od.product_id = products.id
                AND o.status != $cancelledStatus
                AND o.deleted_at IS NULL
                AND DATE(o.order_date) BETWEEN '$previous_start' AND '$previous_end'
            ) as previous_orders"),

                        // Current Views
                        DB::raw("(
                SELECT COUNT(pv.id)
                FROM product_views pv
                WHERE pv.product_id = products.id
                AND DATE(pv.created_at) BETWEEN '$current_start' AND '$current_end'
            ) as current_views"),

                        // Previous Views
                        DB::raw("(
                SELECT COUNT(pv.id)
                FROM product_views pv
                WHERE pv.product_id = products.id
                AND DATE(pv.created_at) BETWEEN '$previous_start' AND '$previous_end'
            ) as previous_views"),

                        // Current Cart
                        DB::raw("(
                SELECT COUNT(c.id)
                FROM carts c
                WHERE c.product_id = products.id
                AND c.status = 1
                AND DATE(c.created_at) BETWEEN '$current_start' AND '$current_end'
            ) as current_cart"),

                        // Previous Cart
                        DB::raw("(
                SELECT COUNT(c.id)
                FROM carts c
                WHERE c.product_id = products.id
                AND c.status = 1
                AND DATE(c.created_at) BETWEEN '$previous_start' AND '$previous_end'
            ) as previous_cart"),

                        // Current Wishlist
                        DB::raw("(
                SELECT COUNT(w.id)
                FROM wishlists w
                WHERE w.product_id = products.id
                AND DATE(w.created_at) BETWEEN '$current_start' AND '$current_end'
            ) as current_wishlist"),

                        // Previous Wishlist
                        DB::raw("(
                SELECT COUNT(w.id)
                FROM wishlists w
                WHERE w.product_id = products.id
                AND DATE(w.created_at) BETWEEN '$previous_start' AND '$previous_end'
            ) as previous_wishlist"),

                        // Trending Score
                        DB::raw("(
                (
                    (
                        (SELECT COUNT(od.id) FROM order_details od
                            JOIN orders o ON od.order_id = o.id AND o.type = 'sales'
                            WHERE od.product_id = products.id
                            AND o.status != $cancelledStatus
                            AND o.deleted_at IS NULL
                            AND DATE(o.order_date) BETWEEN '$current_start' AND '$current_end')
                        -
                        (SELECT COUNT(od.id) FROM order_details od
                            JOIN orders o ON od.order_id = o.id AND o.type = 'sales'
                            WHERE od.product_id = products.id
                            AND o.status != $cancelledStatus
                            AND o.deleted_at IS NULL
                            AND DATE(o.order_date) BETWEEN '$previous_start' AND '$previous_end')
                    )
                    /
                    (
                        (SELECT COUNT(od.id) FROM order_details od
                            JOIN orders o ON od.order_id = o.id AND o.type = 'sales'
                            WHERE od.product_id = products.id
                            AND o.status != $cancelledStatus
                            AND o.deleted_at IS NULL
                            AND DATE(o.order_date) BETWEEN '$previous_start' AND '$previous_end')
                        + 1
                    )
                    * 100 * 0.4
                )
                +
                (
                    (
                        (SELECT COUNT(pv.id) FROM product_views pv
                            WHERE pv.product_id = products.id
                            AND DATE(pv.created_at) BETWEEN '$current_start' AND '$current_end')
                        -
                        (SELECT COUNT(pv.id) FROM product_views pv
                            WHERE pv.product_id = products.id
                            AND DATE(pv.created_at) BETWEEN '$previous_start' AND '$previous_end')
                    )
                    /
                    (
                        (SELECT COUNT(pv.id) FROM product_views pv
                            WHERE pv.product_id = products.id
                            AND DATE(pv.created_at) BETWEEN '$previous_start' AND '$previous_end')
                        + 1
                    )
                    * 100 * 0.2
                )
                +
                (
                    (
                        (SELECT COUNT(c.id) FROM carts c
                            WHERE c.product_id = products.id AND c.status = 1
                            AND DATE(c.created_at) BETWEEN '$current_start' AND '$current_end')
                        -
                        (SELECT COUNT(c.id) FROM carts c
                            WHERE c.product_id = products.id AND c.status = 1
                            AND DATE(c.created_at) BETWEEN '$previous_start' AND '$previous_end')
                    )
                    /
                    (
                        (SELECT COUNT(c.id) FROM carts c
                            WHERE c.product_id = products.id AND c.status = 1
                            AND DATE(c.created_at) BETWEEN '$previous_start' AND '$previous_end')
                        + 1
                    )
                    * 100 * 0.2
                )
                +
                (
                    (
                        (SELECT COUNT(w.id) FROM wishlists w
                            WHERE w.product_id = products.id
                            AND DATE(w.created_at) BETWEEN '$current_start' AND '$current_end')
                        -
                        (SELECT COUNT(w.id) FROM wishlists w
                            WHERE w.product_id = products.id
                            AND DATE(w.created_at) BETWEEN '$previous_start' AND '$previous_end')
                    )
                    /
                    (
                        (SELECT COUNT(w.id) FROM wishlists w
                            WHERE w.product_id = products.id
                            AND DATE(w.created_at) BETWEEN '$previous_start' AND '$previous_end')
                        + 1
                    )
                    * 100 * 0.2
                )
            ) as trending_score"),
                    ])
                    ->having('trending_score', '>', $minScore)
                    ->orderByDesc('trending_score');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'current_orders'   => $product->current_orders,
                        'previous_orders'  => $product->previous_orders,
                        'current_views'    => $product->current_views,
                        'current_cart'     => $product->current_cart,
                        'current_wishlist' => $product->current_wishlist,
                        'trending_score'   => round($product->trending_score, 2),
                        'product'          => $product,
                    ];
                });
            }
            if ($type === 'high_profit_products') {
                $dateCondition = "";
                $bindings = [Status::Cancelled->value];

                if (!empty($params['date_from'])) {
                    $dateCondition .= " AND o.order_date >= ?";
                    $bindings[] = $params['date_from'];
                }
                if (!empty($params['date_to'])) {
                    $dateCondition .= " AND o.order_date <= ?";
                    $bindings[] = $params['date_to'];
                }

                $query = Product::query()
                    ->select('products.id', 'products.title', 'products.thumbnail_95')

                    // ✅ Cost Priority:
                    // 1. Latest completed purchase price
                    // 2. products.purchase_price বা variation.purchase_price (variation_id থাকলে)
                    // 3. regular_price - 100
                    ->selectRaw("
    COALESCE(
        NULLIF((
            SELECT pd.purchase_price 
            FROM purchase_details pd 
            JOIN purchases p ON p.id = pd.purchase_id 
            WHERE pd.product_id = products.id 
              AND p.status = ?
            ORDER BY p.purchase_date DESC, p.id DESC 
            LIMIT 1
        ), 0),

        NULLIF((
            SELECT pv.purchase_price 
            FROM product_variations pv 
            WHERE pv.product_id = products.id 
              AND pv.deleted_at IS NULL
            ORDER BY pv.id ASC 
            LIMIT 1
        ), 0),

 
        NULLIF(
            CASE 
                WHEN products.type = 'single' 
                    THEN products.regular_price - 100
                ELSE (
                    SELECT pv.regular_price - 100
                    FROM product_variations pv 
                    WHERE pv.product_id = products.id 
                      AND pv.deleted_at IS NULL
                    ORDER BY pv.id ASC 
                    LIMIT 1
                )
            END
        , 0)

    ) as latest_cost
", [Status::Completed->value])
                    // Total Qty Sold
                    ->selectRaw("
            IFNULL((
                SELECT SUM(od.quantity) 
                FROM order_details od 
                JOIN orders o ON o.id = od.order_id 
                WHERE od.product_id = products.id 
                  AND o.status != ?
                  $dateCondition
            ), 0) as total_qty_sold
        ", $bindings)

                    // ✅ Total Revenue — od.total ব্যবহার (discount + tax সব included)
                    ->selectRaw("
            IFNULL((
                SELECT SUM(od.total) 
                FROM order_details od 
                JOIN orders o ON o.id = od.order_id 
                WHERE od.product_id = products.id 
                  AND o.status != ?
                  $dateCondition
            ), 0) as total_revenue
        ", $bindings)

                    ->havingRaw('(total_revenue - (total_qty_sold * latest_cost)) > 0');

                if (!empty($params['min_profit_amount'])) {
                    $query->havingRaw('(total_revenue - (total_qty_sold * latest_cost)) >= ?', [(float) $params['min_profit_amount']]);
                }

                $query->orderByRaw('(total_revenue - (total_qty_sold * latest_cost)) DESC');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    $profit = $product->total_revenue - ($product->total_qty_sold * $product->latest_cost);
                    return (object)[
                        'product_id'     => $product->id,
                        'total_qty_sold' => $product->total_qty_sold,
                        'total_revenue'  => $product->total_revenue,
                        'total_profit'   => $profit,
                        'latest_cost'    => $product->latest_cost,
                        'product'        => $product,
                    ];
                });
            }
            // ==========================================
            //  HIGH PROFIT MARGIN PRODUCTS
            // ==========================================
            if ($type === 'high_profit_margin_products') {
                $dateCondition = "";
                $bindings = [Status::Cancelled->value];

                if (!empty($params['date_from'])) {
                    $dateCondition .= " AND o.order_date >= ?";
                    $bindings[] = $params['date_from'];
                }
                if (!empty($params['date_to'])) {
                    $dateCondition .= " AND o.order_date <= ?";
                    $bindings[] = $params['date_to'];
                }

                $query = Product::query()
                    ->select('products.id', 'products.title', 'products.thumbnail_95')
                    ->selectRaw("
            COALESCE(
                NULLIF((SELECT pd.purchase_price FROM purchase_details pd JOIN purchases p ON p.id = pd.purchase_id WHERE pd.product_id = products.id AND p.status = ? ORDER BY p.purchase_date DESC, p.id DESC LIMIT 1), 0),
                NULLIF((SELECT pv.purchase_price FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL ORDER BY pv.id ASC LIMIT 1), 0),
                NULLIF(CASE WHEN products.type = 'single' THEN products.regular_price - 100 ELSE (SELECT pv.regular_price - 100 FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL ORDER BY pv.id ASC LIMIT 1) END, 0)
            ) as latest_cost
        ", [Status::Completed->value])
                    ->selectRaw("IFNULL((SELECT SUM(od.quantity) FROM order_details od JOIN orders o ON o.id = od.order_id WHERE od.product_id = products.id AND o.status != ? $dateCondition), 0) as total_qty_sold", $bindings)
                    ->selectRaw("IFNULL((SELECT SUM(od.total) FROM order_details od JOIN orders o ON o.id = od.order_id WHERE od.product_id = products.id AND o.status != ? $dateCondition), 0) as total_revenue", $bindings);

                //  Margin Calculation Logic
                $query->havingRaw('total_revenue > 0'); // Prevent division by zero

                if (!empty($params['min_margin'])) {
                    $query->havingRaw('(((total_revenue - (total_qty_sold * latest_cost)) / total_revenue) * 100) >= ?', [(float) $params['min_margin']]);
                } else {
                    $query->havingRaw('(((total_revenue - (total_qty_sold * latest_cost)) / total_revenue) * 100) > 0'); // Default positive margin
                }

                $query->orderByRaw('(((total_revenue - (total_qty_sold * latest_cost)) / total_revenue) * 100) DESC');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    $cost = $product->total_qty_sold * $product->latest_cost;
                    $profit = $product->total_revenue - $cost;
                    $margin = $product->total_revenue > 0 ? ($profit / $product->total_revenue) * 100 : 0;

                    return (object)[
                        'product_id'     => $product->id,
                        'total_qty_sold' => $product->total_qty_sold,
                        'total_profit'   => round($profit, 2),
                        'margin_percent' => round($margin, 2),
                        'product'        => $product,
                    ];
                });
            }

            // ==========================================
            //  LOW PROFIT MARGIN PRODUCTS
            // ==========================================
            if ($type === 'low_profit_margin_products') {
                $dateCondition = "";
                $bindings = [Status::Cancelled->value];

                if (!empty($params['date_from'])) {
                    $dateCondition .= " AND o.order_date >= ?";
                    $bindings[] = $params['date_from'];
                }
                if (!empty($params['date_to'])) {
                    $dateCondition .= " AND o.order_date <= ?";
                    $bindings[] = $params['date_to'];
                }

                $query = Product::query()
                    ->select('products.id', 'products.title', 'products.thumbnail_95')
                    ->selectRaw("
            COALESCE(
                NULLIF((SELECT pd.purchase_price FROM purchase_details pd JOIN purchases p ON p.id = pd.purchase_id WHERE pd.product_id = products.id AND p.status = ? ORDER BY p.purchase_date DESC, p.id DESC LIMIT 1), 0),
                NULLIF((SELECT pv.purchase_price FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL ORDER BY pv.id ASC LIMIT 1), 0),
                NULLIF(CASE WHEN products.type = 'single' THEN products.regular_price - 100 ELSE (SELECT pv.regular_price - 100 FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL ORDER BY pv.id ASC LIMIT 1) END, 0)
            ) as latest_cost
        ", [Status::Completed->value])
                    ->selectRaw("IFNULL((SELECT SUM(od.quantity) FROM order_details od JOIN orders o ON o.id = od.order_id WHERE od.product_id = products.id AND o.status != ? $dateCondition), 0) as total_qty_sold", $bindings)
                    ->selectRaw("IFNULL((SELECT SUM(od.total) FROM order_details od JOIN orders o ON o.id = od.order_id WHERE od.product_id = products.id AND o.status != ? $dateCondition), 0) as total_revenue", $bindings);

                // ✅ Low Margin Logic
                $query->havingRaw('total_revenue > 0'); // Prevent division by zero

                if (!empty($params['max_margin'])) {
                    $query->havingRaw('(((total_revenue - (total_qty_sold * latest_cost)) / total_revenue) * 100) <= ?', [(float) $params['max_margin']]);
                }

                $query->orderByRaw('(((total_revenue - (total_qty_sold * latest_cost)) / total_revenue) * 100) ASC'); // সবচেয়ে কম মার্জিন আগে আসবে

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    $cost = $product->total_qty_sold * $product->latest_cost;
                    $profit = $product->total_revenue - $cost;
                    $margin = $product->total_revenue > 0 ? ($profit / $product->total_revenue) * 100 : 0;

                    return (object)[
                        'product_id'     => $product->id,
                        'total_qty_sold' => $product->total_qty_sold,
                        'total_profit'   => round($profit, 2),
                        'margin_percent' => round($margin, 2),
                        'product'        => $product,
                    ];
                });
            }

            // ==========================================
            //  HIGH DISCOUNT PRODUCTS
            // ==========================================
            if ($type === 'high_discount_products') {
                $query = Product::query()
                    ->select('products.id', 'products.title', 'products.thumbnail_95')
                    ->selectRaw("
            COALESCE(
                CASE WHEN products.type = 'single' THEN
                    CASE WHEN products.discount_type = 'percent' THEN products.discount
                         WHEN products.discount_type = 'flat' AND products.regular_price > 0 THEN (products.discount / products.regular_price) * 100
                         ELSE 0 END
                ELSE
                    (SELECT MAX(
                        CASE WHEN pv.discount_type = 'percent' THEN pv.discount
                             WHEN pv.discount_type = 'flat' AND pv.regular_price > 0 THEN (pv.discount / pv.regular_price) * 100
                             ELSE 0 END
                    ) FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL)
                END, 0) as max_discount_percent
        ");

                $query->havingRaw('max_discount_percent > 0');

                if (!empty($params['min_discount'])) {
                    $query->havingRaw('max_discount_percent >= ?', [(float) $params['min_discount']]);
                }


                $query->orderByRaw('max_discount_percent DESC');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'discount_percent' => round($product->max_discount_percent, 2),
                        'product'          => $product,
                    ];
                });
            }

            // ==========================================
            //  FULL PRICE SOLD PRODUCTS (No Discount & Has Sales)
            // ==========================================
            if ($type === 'full_price_sold_products') {
                $dateCondition = "";
                $bindings = [Status::Cancelled->value];

                if (!empty($params['date_from'])) {
                    $dateCondition .= " AND o.order_date >= ?";
                    $bindings[] = $params['date_from'];
                }
                if (!empty($params['date_to'])) {
                    $dateCondition .= " AND o.order_date <= ?";
                    $bindings[] = $params['date_to'];
                }

                $query = Product::query()
                    ->select('products.id', 'products.title', 'products.thumbnail_95')
                    ->selectRaw("
            COALESCE(
                CASE WHEN products.type = 'single' THEN
                    CASE WHEN products.discount_type = 'percent' THEN products.discount
                         WHEN products.discount_type = 'flat' AND products.regular_price > 0 THEN (products.discount / products.regular_price) * 100
                         ELSE 0 END
                ELSE
                    (SELECT MAX(
                        CASE WHEN pv.discount_type = 'percent' THEN pv.discount
                             WHEN pv.discount_type = 'flat' AND pv.regular_price > 0 THEN (pv.discount / pv.regular_price) * 100
                             ELSE 0 END
                    ) FROM product_variations pv WHERE pv.product_id = products.id AND pv.deleted_at IS NULL)
                END, 0) as max_discount_percent
        ")
                    ->selectRaw("
            IFNULL((
                SELECT SUM(od.quantity) 
                FROM order_details od 
                JOIN orders o ON o.id = od.order_id 
                WHERE od.product_id = products.id 
                  AND o.status != ?
                  $dateCondition
            ), 0) as total_qty_sold
        ", $bindings);

                $query->havingRaw('max_discount_percent = 0')
                    ->havingRaw('total_qty_sold > 0');

                $query->orderBy('total_qty_sold', 'DESC');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'total_qty_sold'   => $product->total_qty_sold,
                        'discount_percent' => 0,
                        'product'          => $product,
                    ];
                });
            }

            if ($type === 'most_wishlisted') {
                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95')
                    ->withCount(['wishlists as total_wishlist_count' => function ($q) use ($params) {
                        if (!empty($params['date_from'])) {
                            $q->whereDate('created_at', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('created_at', '<=', $params['date_to']);
                        }
                    }])
                    ->having('total_wishlist_count', '>', 0)
                    ->orderByDesc('total_wishlist_count');

                if (!empty($params['min_count'])) {
                    $query->having('total_wishlist_count', '>=', (int) $params['min_count']);
                }

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'           => $product->id,
                        'total_wishlist_count' => $product->total_wishlist_count,
                        'product'              => $product,
                    ];
                });
            }
            if ($type === 'most_added_to_cart') {
                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95')
                    ->withCount(['carts as total_cart_count' => function ($q) use ($params) {
                        // $q->where('status', 1); // Added to cart
                        if (!empty($params['date_from'])) {
                            $q->whereDate('created_at', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('created_at', '<=', $params['date_to']);
                        }
                    }])
                    ->having('total_cart_count', '>', 0)
                    ->orderByDesc('total_cart_count');

                if (!empty($params['min_count'])) {
                    $query->having('total_cart_count', '>=', (int) $params['min_count']);
                }

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'total_cart_count' => $product->total_cart_count,
                        'product'          => $product,
                    ];
                });
            }
            if ($type === 'frequently_viewed') {
                $query = Product::query()
                    ->select('id', 'title', 'thumbnail_95')
                    ->withCount(['views as total_view_count' => function ($q) use ($params) {
                        if (!empty($params['date_from'])) {
                            $q->whereDate('created_at', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('created_at', '<=', $params['date_to']);
                        }
                    }])
                    ->having('total_view_count', '>', 0)
                    ->orderByDesc('total_view_count');

                if (!empty($params['min_count'])) {
                    $query->having('total_view_count', '>=', (int) $params['min_count']);
                }

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'total_view_count' => $product->total_view_count,
                        'product'          => $product,
                    ];
                });
            }
            if ($type === 'high_cart_low_purchase') {
                $conversionThreshold = !empty($params['conversion_rate']) ? (float) $params['conversion_rate'] : 5.0;

                $dateFrom = $params['date_from'] ?? null;
                $dateTo   = $params['date_to']   ?? null;

                $query = Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.thumbnail_95',
                        'products.regular_price',
                        'products.stock_quantity',
                        'products.stock_status',
                        DB::raw('(
                SELECT COUNT(c.id)
                FROM carts c
                WHERE c.product_id = products.id
                AND c.status = 1
                ' . ($dateFrom ? "AND DATE(c.created_at) >= '$dateFrom'" : '') . '
                ' . ($dateTo   ? "AND DATE(c.created_at) <= '$dateTo'"   : '') . '
            ) as total_cart_count'),
                        DB::raw('(
    SELECT COALESCE(SUM(od.quantity), 0)
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
      AND o.type = "sales"           -- ✅ Fix
    WHERE od.product_id = products.id
    AND o.status != ' . Status::Cancelled->value . '
    AND o.deleted_at IS NULL
    ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
    ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
) as total_orders'),
                        DB::raw('(
    SELECT ROUND(
        COALESCE(SUM(od.quantity), 0) /
        NULLIF((
            SELECT COUNT(c2.id)
            FROM carts c2
            WHERE c2.product_id = products.id
            AND c2.status = 1
            ' . ($dateFrom ? "AND DATE(c2.created_at) >= '$dateFrom'" : '') . '
            ' . ($dateTo   ? "AND DATE(c2.created_at) <= '$dateTo'"   : '') . '
        ), 0) * 100, 2
    )
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
      AND o.type = "sales"           -- ✅ Fix
    WHERE od.product_id = products.id
    AND o.status != ' . Status::Cancelled->value . '
    AND o.deleted_at IS NULL
    ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
    ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
) as conversion_rate'),
                    ])
                    ->having('total_cart_count', '>', 0)
                    ->having(DB::raw('COALESCE(conversion_rate, 0)'), '<', $conversionThreshold)
                    ->orderByDesc('total_cart_count');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'total_cart_count' => $product->total_cart_count,
                        'total_orders'     => $product->total_orders,
                        'conversion_rate'  => $product->conversion_rate ?? 0,
                        'product'          => $product,
                    ];
                });
            }
            if ($type === 'high_view_low_order') {
                $conversionThreshold = !empty($params['conversion_rate']) ? (float) $params['conversion_rate'] : 5.0;

                $dateFrom = $params['date_from'] ?? null;
                $dateTo   = $params['date_to']   ?? null;

                $query = Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.thumbnail_95',
                        'products.regular_price',
                        'products.stock_quantity',
                        'products.stock_status',
                        DB::raw('(
                SELECT COUNT(pv.id)
                FROM product_views pv
                WHERE pv.product_id = products.id
                ' . ($dateFrom ? "AND DATE(pv.created_at) >= '$dateFrom'" : '') . '
                ' . ($dateTo   ? "AND DATE(pv.created_at) <= '$dateTo'"   : '') . '
            ) as total_views'),
                        DB::raw('(
                SELECT COALESCE(SUM(od.quantity), 0)
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                  AND o.type = "sales"
                WHERE od.product_id = products.id
                AND o.status != ' . Status::Cancelled->value . '
                AND o.deleted_at IS NULL
                ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
                ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
            ) as total_orders'),
                        DB::raw('(
    SELECT ROUND(
        COALESCE((
            SELECT COUNT(DISTINCT o.id)
            FROM order_details od
            JOIN orders o ON od.order_id = o.id
               AND o.type = "sales"          -- ✅ Fix 1: missing condition add
            WHERE od.product_id = products.id
            AND o.status != ' . Status::Cancelled->value . '
            AND o.deleted_at IS NULL
            ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
            ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
        ), 0) /
        NULLIF((
            SELECT COUNT(pv2.id)
            FROM product_views pv2
            WHERE pv2.product_id = products.id
            ' . ($dateFrom ? "AND DATE(pv2.created_at) >= '$dateFrom'" : '') . '
            ' . ($dateTo   ? "AND DATE(pv2.created_at) <= '$dateTo'"   : '') . '
        ), 0) * 100, 2
    )
) as conversion_rate'),

                    ])
                    ->having('total_views', '>', 0)
                    ->having('total_orders', '>', 0)
                    ->having(DB::raw('COALESCE(conversion_rate, 0)'), '<', $conversionThreshold)
                    ->orderBy('total_views', 'desc')
                    ->orderBy('total_orders', 'asc');
                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'      => $product->id,
                        'total_views'     => $product->total_views,
                        'total_orders'    => $product->total_orders,
                        'conversion_rate' => $product->conversion_rate,
                        'product'         => $product,
                    ];
                });
            }

            if ($type === 'high_wishlist_low_order') {
                $conversionThreshold = !empty($params['conversion_rate']) ? (float) $params['conversion_rate'] : 5.0;

                $dateFrom = $params['date_from'] ?? null;
                $dateTo   = $params['date_to']   ?? null;

                $query = Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.thumbnail_95',
                        'products.regular_price',
                        'products.stock_quantity',
                        'products.stock_status',
                        DB::raw('(
                SELECT COUNT(w.id)
                FROM wishlists w
                WHERE w.product_id = products.id
                ' . ($dateFrom ? "AND DATE(w.created_at) >= '$dateFrom'" : '') . '
                ' . ($dateTo   ? "AND DATE(w.created_at) <= '$dateTo'"   : '') . '
            ) as total_wishlists'),
                        DB::raw('(
                SELECT COALESCE(SUM(od.quantity), 0)
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                   AND o.type = "sales"
                WHERE od.product_id = products.id
                AND o.status != ' . Status::Cancelled->value . '
                AND o.deleted_at IS NULL
                ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
                ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
            ) as total_orders'),
                        DB::raw('(
    SELECT ROUND(
        COALESCE(SUM(od.quantity), 0) /
        NULLIF((
            SELECT COUNT(w2.id)
            FROM wishlists w2
            WHERE w2.product_id = products.id
            ' . ($dateFrom ? "AND DATE(w2.created_at) >= '$dateFrom'" : '') . '
            ' . ($dateTo   ? "AND DATE(w2.created_at) <= '$dateTo'"   : '') . '
        ), 0) * 100, 2
    )
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
       AND o.type = "sales"   
    WHERE od.product_id = products.id
    AND o.status != ' . Status::Cancelled->value . '
    AND o.deleted_at IS NULL
    ' . ($dateFrom ? "AND DATE(o.order_date) >= '$dateFrom'" : '') . '
    ' . ($dateTo   ? "AND DATE(o.order_date) <= '$dateTo'"   : '') . '
) as conversion_rate'),
                    ])
                    ->having('total_wishlists', '>', 0)
                    ->having('total_orders', '>', 0)
                    ->having(DB::raw('COALESCE(conversion_rate, 0)'), '<', $conversionThreshold)
                    ->orderBy('total_wishlists', 'desc')
                    ->orderBy('total_orders', 'asc');

                if (!empty($params['limit'])) {
                    $query->limit((int) $params['limit']);
                }

                return $query->get()->map(function ($product) {
                    return (object)[
                        'product_id'       => $product->id,
                        'total_wishlists'  => $product->total_wishlists,
                        'total_orders'     => $product->total_orders,
                        'conversion_rate'  => $product->conversion_rate ?? 0,
                        'product'          => $product,
                    ];
                });
            }
            throw ApiException::serverError('Invalid product group filter type');
        } catch (\Exception $e) {
            Log::error('Product group criteria error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch products by criteria');
        }
    }
}


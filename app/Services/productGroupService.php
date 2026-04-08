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

    public function deleteGroup(int $id): bool
    {
        $group = ProductGroup::findOrFail($id);
        $group->delete();
        LogHelper::deleted('customer_group', $group->id, $group->company_id, $group->name);
        return true;
    }

    /**
     * Get Group with Hydrated Customers
     */
    public function getGroupWithProducts(int $id): array
    {
        $group = ProductGroup::findOrFail($id);

        $products = Product::whereIn('id', $group->product_ids ?? [])
            ->select('id', 'title', 'thumbnail')
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
            // date থাকলে Period override করো
            if (!empty($params['date_from']) || !empty($params['date_to'])) {
                unset($filterDetails['Period']);
            }
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
                        $q->select('id', 'title', 'thumbnail', 'regular_price', 'stock_quantity');
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
                    ->select('id', 'title', 'thumbnail');

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

            throw ApiException::serverError('Invalid product group filter type');
        } catch (\Exception $e) {
            Log::error('Product group criteria error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch products by criteria');
        }
    }
}

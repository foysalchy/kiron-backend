<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\CustomerGroup;
use App\Models\Party;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerGroupService
{
    public function getAllGroups(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = CustomerGroup::query();

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

    public function createGroup(array $data): CustomerGroup
    {
        DB::beginTransaction();
        try {

            $group = CustomerGroup::create($data);
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
     * Get Group with Hydrated Customers
     */
    public function getGroupWithCustomers(int $id): array
    {
        $group = CustomerGroup::findOrFail($id);

        $customers = Party::customers()
            ->whereIn('id', $group->customer_ids ?? [])
            ->select('id', 'name', 'phone', 'email')
            ->get();


        $params = $group->filter_parameters ?? [];
        $filterDetails = [];

        if ($group->filter_type === 'category' && !empty($params['category_id'])) {
            $category = MegaCategory::find($params['category_id']);
            if ($category) $filterDetails['Category'] = $category->name;
        }

        if ($group->filter_type === 'brand' && !empty($params['brand_id'])) {
            $brand = Brand::find($params['brand_id']);
            if ($brand) $filterDetails['Brand'] = $brand->name;
        }

        if (!empty($params['product_id'])) {
            $product = Product::find($params['product_id']);
            if ($product) $filterDetails['Product'] = $product->title;
        }
        if ($group->filter_type === 'order') {
            if (!empty($params['first_order_date'])) $filterDetails['From Date'] = $params['first_order_date'];
            if (!empty($params['last_order_date'])) $filterDetails['To Date'] = $params['last_order_date'];
            if (!empty($params['average_order_value'])) $filterDetails['Min AOV'] = $params['average_order_value'];
            if (!empty($params['repeat_customer_count'])) $filterDetails['Min Orders'] = $params['repeat_customer_count'];
            if (!empty($params['order_source'])) $filterDetails['Source'] = strtoupper($params['order_source']);
        }
        return [
            'group' => $group,
            'customers' => $customers,
            'filter_details' => $filterDetails 
        ];
    }

    /**
     * Remove a single customer from the group
     */
    public function removeCustomerFromGroup(int $groupId, int $customerId): CustomerGroup
    {
        $group = CustomerGroup::findOrFail($groupId);

        $currentIds = $group->customer_ids ?? [];

        // Filter out the customerId and re-index the array
        $newIds = array_values(array_filter($currentIds, fn($id) => $id != $customerId));

        $group->update(['customer_ids' => $newIds]);

        LogHelper::updated('customer_group', $group->id, $group->company_id, 'Removed customer ' . $customerId . ' from group ' . $group->name);

        return $group;
    }
    public function toggleStatus(int $id): CustomerGroup
    {
        try {
            $group = CustomerGroup::findOrFail($id);
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
        $group = CustomerGroup::findOrFail($id);
        $group->delete();
        LogHelper::deleted('customer_group', $group->id, $group->company_id, $group->name);
        return true;
    }

    /**
     * Fetch customers and their specific order count based on criteria
     */
    public function getCustomersByCriteria(string $type, array $params = []): Collection
    {
        try {
            $query = Party::customers();

            if (in_array($type, ['category', 'brand'])) {
                $query->withCount('orders')
                    ->whereHas('orders.orderDetails.product', function ($q) use ($type, $params) {
                        if ($type === 'category' && !empty($params['category_id'])) {
                            $q->where(function ($subQ) use ($params) {
                                $subQ->whereJsonContains('mega_category_ids', (string)$params['category_id'])
                                    ->orWhereJsonContains('mega_category_ids', (int)$params['category_id']);
                            });
                        } elseif ($type === 'brand' && !empty($params['brand_id'])) {
                            $q->where('brand_id', $params['brand_id']);
                        }
                        if (!empty($params['product_id'])) {
                            $q->where('id', $params['product_id']);
                        }
                    })
                    ->orderBy('orders_count', 'desc');
            } elseif ($type === 'order') {
                $orderConditions = function ($q) use ($params) {
                    if (!empty($params['first_order_date'])) {
                        $q->whereDate('order_date', '>=', $params['first_order_date']);
                    }
                    if (!empty($params['last_order_date'])) {
                        $q->whereDate('order_date', '<=', $params['last_order_date']);
                    }
                    if (!empty($params['order_source'])) {
                        $q->where('type', $params['order_source']);
                    }
                    if (!empty($params['order_status'])) {
                        $q->where('status', $params['order_status']);
                    }
                };

                $query->whereHas('orders', $orderConditions)
                    ->withCount(['orders as filtered_orders_count' => $orderConditions])
                    ->withAvg(['orders as aov' => $orderConditions], 'grand_total');

                if (!empty($params['repeat_customer_count'])) {
                    $query->having('filtered_orders_count', '>=', (int)$params['repeat_customer_count']);
                }

                if (!empty($params['average_order_value'])) {
                    $query->having('aov', '>=', (float)$params['average_order_value']);
                }

                $query->orderBy('filtered_orders_count', 'desc');
            }

            $customers = $query->get();


            if ($type === 'order') {
                $customers->transform(function ($customer) {
                    $customer->orders_count = $customer->filtered_orders_count;
                    return $customer;
                });
            }

            return $customers;
        } catch (\Exception $e) {
            Log::error('Error fetching customers by criteria: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch criteria customers');
        }
    }
}

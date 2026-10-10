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
    public function getAllGroups(array $filters = [], bool $paginate = true, array $columns = ['*']): Collection|LengthAwarePaginator
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
                ? $query->paginate($filters['per_page'] ?? 15, $columns)
                : $query->get($columns);
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

    public function updateGroup(int $id, array $data): CustomerGroup
    {
        DB::beginTransaction();
        try {
            $group = CustomerGroup::findOrFail($id);
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

    public function restoreGroup(int $id): bool
    {
        $group = CustomerGroup::withTrashed()->findOrFail($id);
        $group->restore();
        LogHelper::updated('customer_group', $group->id, $group->company_id, 'Restored ' . $group->name);
        return true;
    }

    public function forceDeleteGroup(int $id): bool
    {
        $group = CustomerGroup::withTrashed()->findOrFail($id);
        $group->forceDelete();
        LogHelper::deleted('customer_group', $group->id, $group->company_id, 'Permanently deleted ' . $group->name);
        return true;
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
        if ($group->filter_type === 'engagement') {
            if (!empty($params['join_start_date'])) $filterDetails['Joined After'] = $params['join_start_date'];
            if (!empty($params['join_end_date'])) $filterDetails['Joined Before'] = $params['join_end_date'];
            if (!empty($params['inactive_days'])) $filterDetails['Inactive For'] = $params['inactive_days'] . ' Days';
            if (!empty($params['abandoned_cart'])) $filterDetails['Action'] = 'Abandoned Cart';
            if (!empty($params['wishlist_no_purchase'])) $filterDetails['Action'] = 'Wishlist No Purchase';
        }
        if ($group->filter_type === 'coupon') {

            // Specific coupon
            if (!empty($params['coupon_id'])) {
                $coupon = \App\Models\Coupon::find($params['coupon_id']);
                if ($coupon) $filterDetails['Used Coupon'] = $coupon->code;
            }

            // Never used any coupon
            if (!empty($params['never_used_coupon']) && $params['never_used_coupon'] == true) {
                $filterDetails['Coupon Behavior'] = 'Never Used Any Coupon';
            }

            // Min usage count
            if (!empty($params['min_usage_count'])) {
                $filterDetails['Min Coupon Usage'] = 'At least ' . $params['min_usage_count'] . ' time(s)';
            }
        }
        if ($group->filter_type === 'payment' && !empty($params['payment_method'])) {
            $methodLabels = [
                'cash'          => 'Cash',
                'mobile_banking' => 'Mobile Banking',
                'card'          => 'Card',
                'bank_account'  => 'Bank Account',
            ];
            $filterDetails['Payment Method'] = $methodLabels[$params['payment_method']] ?? $params['payment_method'];
        }
        if ($group->filter_type === 'profile') {
            if (!empty($params['filter_by']) && $params['filter_by'] === 'location') {
                if (!empty($params['division']))  $filterDetails['Division'] = $params['division'];
                if (!empty($params['district']))  $filterDetails['District'] = $params['district'];
                if (!empty($params['thana']))     $filterDetails['Thana']    = $params['thana'];
            }
            if (!empty($params['filter_by']) && $params['filter_by'] === 'gender') {
                $filterDetails['Gender'] = ucfirst($params['gender']);
            }
        }
        return [
            'group' => $group,
            'customers' => $customers,
            'filter_details' => $filterDetails
        ];
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
            } elseif ($type === 'engagement') {

                // A. Join Date Filter
                if (!empty($params['join_start_date'])) {
                    $query->whereDate('created_at', '>=', $params['join_start_date']);
                }
                if (!empty($params['join_end_date'])) {
                    $query->whereDate('created_at', '<=', $params['join_end_date']);
                }

                // B. Inactive Customers (No order in last X days)
                if (!empty($params['inactive_days'])) {
                    $days = (int) $params['inactive_days'];
                    $dateThreshold = now()->subDays($days)->format('Y-m-d');

                    $query->whereHas('orders')
                        ->whereDoesntHave('orders', function ($q) use ($dateThreshold) {
                            $q->whereDate('order_date', '>=', $dateThreshold);
                        });
                }


                // C. Abandoned Cart Users
                if (!empty($params['abandoned_cart']) && $params['abandoned_cart'] == true) {

                    $query->whereHas('carts', function ($q) use ($params) {
                        $q->where('status', 1);
                        if (!empty($params['date_from'])) {
                            $q->whereDate('created_at', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('created_at', '<=', $params['date_to']);
                        }
                    });



                    $query->whereDoesntHave('carts', function ($cq) {
                        $cq->where('status', 3);
                    });
                }
                // D. Wishlisted but not purchased
                if (!empty($params['wishlist_no_purchase']) && $params['wishlist_no_purchase'] == true) {
                    $query->whereHas('wishlists', function ($q) use ($params) {
                        if (!empty($params['date_from'])) {
                            $q->whereDate('created_at', '>=', $params['date_from']);
                        }
                        if (!empty($params['date_to'])) {
                            $q->whereDate('created_at', '<=', $params['date_to']);
                        }
                    })
                        ->whereDoesntHave('orders', function ($q) {
                            $q->where('status', '!=', Status::Cancelled->value);
                        })
                        ->orderBy('created_at', 'desc');    
                }
                $query->orderBy('created_at', 'desc');
            } elseif ($type === 'coupon') {

                // Case 1: Never used any coupon
                if (!empty($params['never_used_coupon']) && $params['never_used_coupon'] == true) {
                    $query->whereDoesntHave('orders', function ($orderQuery) {
                        $orderQuery->whereNotNull('coupon_id');
                    });
                }
                // Case 2: Specific coupon or min usage count
                else {
                    if (!empty($params['coupon_id'])) {
                        $couponId = (int) $params['coupon_id'];
                        $query->whereHas('orders', function ($orderQuery) use ($couponId) {
                            $orderQuery->where('coupon_id', $couponId);
                        });
                    }

                    if (!empty($params['min_usage_count'])) {
                        $minCount = (int) $params['min_usage_count'];
                        $query->withCount(['orders as coupon_usage_count' => function ($q) use ($params) {
                            $q->whereNotNull('coupon_id');
                            // If a specific coupon is also selected, scope the count to that coupon
                            if (!empty($params['coupon_id'])) {
                                $q->where('coupon_id', (int) $params['coupon_id']);
                            }
                        }])->having('coupon_usage_count', '>=', $minCount);
                    }
                }

                $query->orderBy('created_at', 'desc');
            } elseif ($type === 'payment') {
                if (!empty($params['payment_method'])) {
                    $query->whereHas('orders', function ($q) use ($params) {
                        $q->where('payment_status', 2)
                            ->whereHas('orderPayments', function ($pq) use ($params) {
                                $pq->where('payment_method', $params['payment_method']);
                            });
                    });
                }
                $query->orderBy('created_at', 'desc');
            } elseif ($type === 'profile') {

                if (!empty($params['filter_by']) && $params['filter_by'] === 'location') {
                    if (!empty($params['division'])) {
                        $query->where('division', $params['division']);
                    }
                    if (!empty($params['district'])) {
                        $query->where('district', $params['district']);
                    }
                    if (!empty($params['thana'])) {
                        $query->where('thana', $params['thana']);
                    }
                }

                if (!empty($params['filter_by']) && $params['filter_by'] === 'gender') {
                    if (!empty($params['gender'])) {
                        $query->where('gender', $params['gender']);
                    }
                }

                $query->orderBy('created_at', 'desc');
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

<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\CourierCheckHistory;
use App\Models\CourierMethod;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Party;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontendOrderService
{
    /**
     * Retrieve orders with all related information
     * 
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $filters = [])
    {
        $query = Order::query()
            ->with([
                'customer:id,name,phone,address,email,thana,district,division',
                'warehouse:id,name',
                'company:id,name',
                'orderDetails.product:id,title,sku_code,thumbnail',
                'orderDetails.variation.attributes.attributeGroup',
                'orderDetails.variation.attributes.attributeValue',
                'orderNotes',
                'coupon:id,code,discount_value'
            ])
            ->select('orders.*');

        // Apply filters


        if (isset($filters['payment_status'])) {
            $query->where('orders.payment_status', $filters['payment_status']);
        }

        $needsCustomerJoin = !empty($filters['customer_name']) || !empty($filters['customer_phone']) || !empty($filters['search']);
        if ($needsCustomerJoin) {
            $query->leftJoin('parties', 'orders.customer_id', '=', 'parties.id');
        }

        if (!empty($filters['customer_name'])) {
            $query->where('parties.name', 'like', '%' . $filters['customer_name'] . '%');
        }

        if (!empty($filters['customer_phone'])) {
            $rawPhone = trim($filters['customer_phone']);
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
                $cleanPhone = '0' . substr($cleanPhone, 3);
            }

            $query->where(function ($q) use ($rawPhone, $cleanPhone) {
                $q->where('parties.phone', 'like', '%' . $rawPhone . '%');
                if (!empty($cleanPhone)) {
                    $q->orWhere('parties.phone', 'like', '%' . $cleanPhone . '%')
                      ->orWhere('orders.shipping_address', 'like', '%' . $cleanPhone . '%');
                }
            });
        }

        if (!empty($filters['order_no'])) {
            $query->where('orders.order_no', 'like', '%' . $filters['order_no'] . '%');
        }

        if (!empty($filters['product_name']) || !empty($filters['product_sku'])) {
            $query->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id')
                  ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
                  ->distinct();
                  
            $query->where(function($q) use ($filters) {
                if (!empty($filters['product_name'])) {
                    $q->where('products.title', 'like', '%' . $filters['product_name'] . '%');
                }
                if (!empty($filters['product_sku'])) {
                    $q->where('products.sku_code', 'like', '%' . $filters['product_sku'] . '%');
                }
            });
        }

        if (!empty($filters['order_date'])) {
            $query->whereDate('orders.order_date', $filters['order_date']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('orders.order_date', [$filters['date_from'], $filters['date_to']]);
        }

        if (!empty($filters['source'])) {
            $sourceFilter = strtolower($filters['source']);
            $query->where(function ($q) use ($sourceFilter) {
                $q->where('orders.source', $sourceFilter)
                  ->orWhere('orders.source_info->source', 'like', '%' . $sourceFilter . '%')
                  ->orWhere('orders.pixel_source_info->source', 'like', '%' . $sourceFilter . '%');

                if ($sourceFilter === 'facebook') {
                    $q->orWhereNotNull('orders.pixel_source_info->fbc')
                      ->orWhere('orders.pixel_source_info->url', 'like', '%fbclid=%');
                }
            });
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('orders.warehouse_id', $filters['warehouse_id']);
        }

        // Search functionality
        if (!empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $cleanPhone = preg_replace('/[^0-9]/', '', $searchTerm);
            if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
                $cleanPhone = '0' . substr($cleanPhone, 3);
            }

            $query->where(function ($q) use ($searchTerm, $cleanPhone) {
                $q->where('orders.order_no', 'like', '%' . $searchTerm . '%')
                    ->orWhere('orders.reference_no', 'like', '%' . $searchTerm . '%')
                    ->orWhere('parties.name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('parties.phone', 'like', '%' . $searchTerm . '%');

                // If user searched with formatted phone (e.g. 01703-236221, +8801703-236221)
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 4) {
                    $q->orWhere('parties.phone', 'like', '%' . $cleanPhone . '%')
                      ->orWhere('orders.shipping_address', 'like', '%' . $cleanPhone . '%');
                }
            });
        }

        // Duplicate orders filter: filter orders where same customer or phone has placed multiple orders
        if (!empty($filters['is_duplicate']) && $filters['is_duplicate'] !== 'all' && $filters['is_duplicate'] != '0') {
            $companyId = auth()->user()?->company_id;

            // 1. Customer IDs that have placed > 1 orders
            $dupCustomerIds = DB::table('orders')
                ->whereNull('deleted_at')
                ->whereNotNull('customer_id')
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('customer_id');

            // 2. Phone numbers (from parties.phone or shipping_address) that have > 1 orders
            $dupPhones = DB::table('orders')
                ->leftJoin('parties', 'orders.customer_id', '=', 'parties.id')
                ->whereNull('orders.deleted_at')
                ->when($companyId, fn($q) => $q->where('orders.company_id', $companyId))
                ->select(DB::raw("COALESCE(parties.phone, JSON_UNQUOTE(JSON_EXTRACT(orders.shipping_address, '$.phone'))) as phone_no"))
                ->groupBy(DB::raw("COALESCE(parties.phone, JSON_UNQUOTE(JSON_EXTRACT(orders.shipping_address, '$.phone')))"))
                ->havingRaw('COUNT(*) > 1')
                ->havingRaw("phone_no IS NOT NULL AND phone_no != ''")
                ->pluck('phone_no');

            $query->where(function ($q) use ($dupCustomerIds, $dupPhones) {
                $hasCondition = false;
                if ($dupCustomerIds->isNotEmpty()) {
                    $q->whereIn('orders.customer_id', $dupCustomerIds);
                    $hasCondition = true;
                }
                if ($dupPhones->isNotEmpty()) {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $q->$method(function ($pQ) use ($dupPhones) {
                        $pQ->whereHas('customer', fn($cQ) => $cQ->whereIn('phone', $dupPhones))
                           ->orWhere(function ($sQ) use ($dupPhones) {
                               foreach ($dupPhones as $ph) {
                                   $sQ->orWhere('orders.shipping_address', 'like', '%' . $ph . '%');
                               }
                           });
                    });
                    $hasCondition = true;
                }
                if (!$hasCondition) {
                    $q->whereRaw('0 = 1');
                }
            });
        }

        // Order by latest first
        $query->orderBy('orders.created_at', 'desc');

        // Paginate results
        $perPage = $filters['per_page'] ?? 20;
        $query->when(
            isset($filters['status']),
            function ($q) use ($filters) {
                if ($filters['status'] == Status::Draft->value) {
                    $q->where('orders.status', Status::Draft->value);
                } else {
                    $q->where('orders.status', $filters['status']);
                }
            },
            function ($q) {
                $q->where('orders.status', '!=', Status::Draft->value);
            }
        );
        $orders = $query->paginate($perPage);

        // Pre-fetch customer total orders to avoid N+1
        $customerIds = $orders->getCollection()->pluck('customer_id')->filter()->unique()->values()->toArray();
        $customerTotalOrdersMap = [];
        if (!empty($customerIds)) {
            $customerTotalOrdersMap = Order::whereIn('customer_id', $customerIds)
                ->select('customer_id', DB::raw('count(*) as total'))
                ->groupBy('customer_id')
                ->pluck('total', 'customer_id')
                ->toArray();
        }

        // Collect all phones (from customer or shipping address) to avoid N+1 for fake count
        $phonesForFake = $orders->getCollection()->map(function ($order) {
            return $order->customer?->phone ?? ($order->shipping_address['phone'] ?? null);
        })->filter()->unique()->values()->toArray();

        $fakeOrderCountMap = [];
        if (!empty($phonesForFake)) {
            $fakeOrders = Order::where('status', Status::Fake->value)
                ->where(function ($q) use ($phonesForFake) {
                    $q->whereHas('customer', function ($cQ) use ($phonesForFake) {
                        $cQ->whereIn('phone', $phonesForFake);
                    });
                    foreach ($phonesForFake as $phone) {
                        $q->orWhere('shipping_address', 'LIKE', '%"phone":"' . $phone . '"%')
                          ->orWhere('shipping_address', 'LIKE', '%"phone": "' . $phone . '"%');
                    }
                })
                ->get();

            foreach ($phonesForFake as $phone) {
                $fakeOrderCountMap[$phone] = $fakeOrders->filter(function ($fakeOrder) use ($phone) {
                    $fakePhone = $fakeOrder->customer?->phone ?? ($fakeOrder->shipping_address['phone'] ?? null);
                    return $fakePhone === $phone;
                })->count();
            }
        }

        $phones = $orders->getCollection()
            ->pluck('customer.phone')
            ->filter()
            ->unique()
            ->values();
        $courierHistories = CourierCheckHistory::whereIn('phone', $phones)
            ->get()
            ->keyBy('phone');

        // Pre-calculate order counts for each IP address to avoid N+1 queries
        $ips = $orders->getCollection()->map(function ($order) {
            return $order->type !== 'pos' ? ($order->source_info['ip'] ?? $order->pixel_source_info['ip'] ?? null) : null;
        })->filter()->unique()->values()->toArray();

        $ipOrderCountMap = [];
        if (!empty($ips)) {
            $currentCompanyId = auth()->user()->company_id ?? null;
            foreach ($ips as $ip) {
                $ipQuery = Order::query();
                if ($currentCompanyId) {
                    $ipQuery->where('company_id', $currentCompanyId);
                }
                $ipOrderCountMap[$ip] = $ipQuery->where(function ($q) use ($ip) {
                    $q->where('source_info->ip', $ip)
                      ->orWhere('pixel_source_info->ip', $ip)
                      ->orWhere('source_info', 'LIKE', '%"ip":"' . $ip . '"%')
                      ->orWhere('source_info', 'LIKE', '%"ip": "' . $ip . '"%');
                })->count();
            }
        }
            
        // Transform the data to include calculated fields
        $orders->getCollection()->transform(function ($order) use ($courierHistories, $customerTotalOrdersMap, $fakeOrderCountMap, $ipOrderCountMap) {
            $customerTotalOrders = 1;
            $fakeOrderCount = 0;
            
            $phone = $order->customer?->phone ?? $order->shipping_address['phone'] ?? null;
            
            if ($order->customer_id) {
                $customerTotalOrders = $customerTotalOrdersMap[$order->customer_id] ?? 1;
            }
            
            if ($phone) {
                $baseFakeCount = $fakeOrderCountMap[$phone] ?? 0;
                $fakeOrderCount = $baseFakeCount;
                if ($order->status === Status::Fake->value) {
                    $fakeOrderCount = max(0, $baseFakeCount - 1);
                }
            }

            // ✅ courier history lookup
            $courierHistoryPhone = $order->customer?->phone;
            $courierHistory = $courierHistoryPhone ? ($courierHistories[$courierHistoryPhone] ?? null) : null;

            $orderIp = $order->type !== 'pos' ? ($order->source_info['ip'] ?? $order->pixel_source_info['ip'] ?? null) : null;
            $ipOrderCount = $orderIp ? ($ipOrderCountMap[$orderIp] ?? 1) : 0;

            return [
                'id' => $order->id,
                'orderNumber' => $order->order_no,
                'customerName' => $order->customer?->name ?? 'Walk-in Customer',
                'customerPhone' => $order->customer?->phone ?? '',
                'customer_id' => $order->customer?->id ?? '',
                'address' => $order->customer?->address ?? '',
                'shipping_address' => $order->shipping_address,
                'customer' => $order->customer,
                'paymethod' => $this->getPaymentMethod($order),
                'totalorder' => $customerTotalOrders,
                'fake_order_count' => $fakeOrderCount,
                'status' => $this->getStatusLabel($order->status),
                'order_status' => $order->status,
                'note' => $this->resolveDisplayNote($order->note, $order->is_otp_verified),
                'is_otp_verified' => $order->is_otp_verified,
                'paymentStatus' => $this->getPaymentStatusLabel($order->payment_status),
                'source_info' => $order->source_info,
                'source' => $this->resolveOrderDisplaySource($order),
                'ip_address' => $orderIp,
                'ip_order_count' => $ipOrderCount,
                'items' => $order->orderDetails->map(function ($detail) {
                    $item = [
                        'id' => $detail->id,
                        'title' => $detail->product?->title ?? 'Unknown Product',
                        'product_id' => $detail->product_id,
                        'variation_id' => $detail->variation_id,
                        'sku' => $detail->product?->sku_code ?? '',
                        'price' => (float) $detail->unit_price,
                        'quantity' => $detail->quantity,
                        'discount' => (float) $detail->discount,
                        'tax' => (float) $detail->tax,
                        'total' => (float) $detail->total,
                        'image' => $detail->product?->thumbnail ?? null,
                    ];

                    if ($detail->variation) {
                        $item['variation'] = [
                            'id' => $detail->variation->id,
                            'sku' => $detail->variation->sku,
                            'attributes' => $detail->variation->attributes->map(function ($attr) {
                                return [
                                    'id' => $attr->id,
                                    'group_name' => $attr->attributeGroup?->name ?? '',
                                    'value_name' => $attr->attributeValue?->name ?? '',
                                ];
                            }),
                        ];

                        if ($detail->variation->sku) {
                            $item['sku'] = $detail->variation->sku;
                        }
                    } else {
                        $item['variation'] = null;
                    }

                    return $item;
                }),
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) ($order->discount_on_all + $order->coupon_discount),
                'otherCharges' => (float) $order->other_charges,
                'roundOff' => (float) $order->round_off,
                'totalPrice' => (float) $order->grand_total,
                'paidAmount' => (float) $order->payment_amount,
                'notes' => $this->formatOrderNotes($order->orderNotes, $order->is_otp_verified),
                'orderDate' => $order->order_date,
                'timeAgo' => $order->created_at->diffForHumans(),
                'warehouse' => $order->warehouse?->name ?? '',
                'type' => $order->type,
                'reference_no' => $order->reference_no,
                'is_walk_in' => (bool) $order->is_walk_in,
                'courierInfo' => $this->getCourierInfo($order),
                'assigned_to' => $order->assigned_to,
                'due_amount' => (float) ($order->grand_total - $order->payment_amount),
                'courierHistory' => $courierHistory ? [
                    'summary' => $courierHistory->response_data['summary'] ?? null,
                    'checked_at' => $courierHistory->checked_at,
                    'next_allowed_at' => $courierHistory->checked_at?->addHours(2),

                ] : null,
            ];
        });

        return $orders;
    }
    public function getOrderCountsByStatus()
    {

        return Order::query()
            ->whereNot('status', Status::Draft->value)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    public function getOrderAmountsByStatus()
    {
        return Order::query()
            ->whereNot('status', Status::Draft->value)
            ->select('status', DB::raw('COALESCE(SUM(grand_total), 0) as total_amount'))
            ->groupBy('status')
            ->pluck('total_amount', 'status');
    }

    /**
     * Get order by ID with all details
     */
    public function getOrderById($orderId)
    {
        $order = Order::with([
            'customer:id,name,phone,address,email,thana,district,division',
            'warehouse:id,name',
            'company:id,name,email,phone,logo',
            'actionLogs.user:id,name,email',
            'orderDetails.product:id,title,sku_code,thumbnail',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
            'orderPayments',
            'orderNotes' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'coupon:id,code,discount_value'
        ])
            ->findOrFail($orderId);


        $assignedUsers = [];
        if (!empty($order->assigned_to)) {
            $assignedUsers = \App\Models\User::whereIn('id', $order->assigned_to)
                ->select('id', 'name', 'email')
                ->get()
                ->map(fn($u) => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                ])
                ->toArray();
        }

        return $this->transformOrderData($order, $assignedUsers);
    }
    public function getEditOrder(int $id,): Order
    {
        $order = Order::with([
            'warehouse',
            'customer',
            'coupon',
            'orderDetails.product',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
            'orderPayments'
        ])->find($id);

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }

    public function getCustomerOrder($customerId)
    {
        $order = Order::where('customer_id', $customerId)->get();

        return $order;
    }

    public function getOrdersByIp($ip, $companyId = null)
    {
        $query = Order::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->where(function ($q) use ($ip) {
            $q->where('source_info->ip', $ip)
              ->orWhere('pixel_source_info->ip', $ip)
              ->orWhere('source_info', 'LIKE', '%"ip":"' . $ip . '"%')
              ->orWhere('source_info', 'LIKE', '%"ip": "' . $ip . '"%');
        })
        ->latest()
        ->get();
    }

    /**
     * Get payment method from order
     */
    private function getPaymentMethod($order)
    {

        return $order->orderPayments()->latest()->first()?->payment_method ?? 'Cash';
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status): string
    {
        $statusEnum = $status instanceof Status ? $status : Status::tryFrom((int) $status);

        return $statusEnum?->label() ?? 'Unknown';
    }
    /**
     * Get payment status label
     */
    private function getPaymentStatusLabel($paymentStatus)
    {
        $statusLabels = [
            0 => 'Unpaid',
            1 => 'Partial',
            2 => 'Paid',
        ];

        return $statusLabels[$paymentStatus] ?? 'Unknown';
    }

    /**
     * Get courier information (placeholder - implement based on your courier tracking)
     */
    private function getCourierInfo($order)
    {
        // If no courier_info stored, return null
        if (empty($order->courier_info)) {
            return null;
        }

        // Decode JSON from courier_info field
        $info = is_array($order->courier_info)
            ? $order->courier_info
            : json_decode($order->courier_info, true);

        if (!$info) {
            return null;
        }

        $courierName = $info['courier_name'] ?? null;

        // Map courier status to readable label
        $statusMap = [
            'in_review'     => 'In Review',
            'pending'       => 'Pending',
            'picked'        => 'Picked',
            'in_transit'    => 'In Transit',
            'delivered'     => 'Delivered',
            'partial_delivery' => 'Partial Delivery',
            'cancelled'     => 'Cancelled',
            'hold'          => 'On Hold',
            'unknown'       => 'Unknown',
        ];

        $rawStatus = $info['status'] ?? 'unknown';
        $readableStatus = $statusMap[$rawStatus] ?? ucfirst(str_replace('_', ' ', $rawStatus));

        return [
            'name'           => $courierName,
            'status'         => $readableStatus,
            'raw_status'     => $rawStatus,
            'trackingCode'   => $info['tracking_code'] ?? null,
            'consignmentId'  => $info['consignment_id'] ?? null,
            'note'           => $info['note'] ?? null,
            'appliedAt'      => $info['applied_at'] ?? null,
            'is_packed'      => !empty($info['is_packed']),
            'courier_charge' => isset($info['courier_charge']) ? (float)$info['courier_charge'] : (isset($info['delivery_charge']) ? (float)$info['delivery_charge'] : null),
            'delivery_charge'=> isset($info['delivery_charge']) ? (float)$info['delivery_charge'] : (isset($info['courier_charge']) ? (float)$info['courier_charge'] : null),
        ];
    }
    private function getCourierMethod()
    {

        return CourierMethod::select('id', 'name')->get();
    }
    /**
     * Transform single order data
     */
    private function transformOrderData($order, $assignedUsers, $customerStats = null)
    {
        $customerTotalOrders = 1;
        $deliveredCount = 0;
        $returnedCount = 0;
        $cancelledCount = 0;
        $successRatio = 0;

        if ($order->customer_id) {
            if ($customerStats && isset($customerStats[$order->customer_id])) {
                // list (getOrders) থেকে call হলে bulk data ব্যবহার হবে
                $stats = $customerStats[$order->customer_id];

                $customerTotalOrders = $stats->sum('total');
                $deliveredCount = $stats->firstWhere('status', Status::Delivered->value)?->total ?? 0;
                $returnedCount = $stats->firstWhere('status', Status::Returned->value)?->total ?? 0;
                $cancelledCount = $stats->firstWhere('status', Status::Cancelled->value)?->total ?? 0;
            } else {

                $statsQuery = Order::where('customer_id', $order->customer_id)
                    ->where('status', '!=', Status::Draft->value)
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get();

                $customerTotalOrders = $statsQuery->sum('total');
                $deliveredCount = $statsQuery->firstWhere('status', Status::Delivered->value)?->total ?? 0;
                $returnedCount = $statsQuery->firstWhere('status', Status::Returned->value)?->total ?? 0;
                $cancelledCount = $statsQuery->firstWhere('status', Status::Cancelled->value)?->total ?? 0;
            }

            $successRatio = $customerTotalOrders > 0
                ? round(($deliveredCount / $customerTotalOrders) * 100, 1)
                : 0;
        }

        return [
            'id' => $order->id,
            'orderNumber' => $order->order_no,
            'order_date' => $order->order_date,
            'reference_no' => $order->reference_no,
            'warehouse_id' => $order->warehouse_id,
            'customerName' => $order->customer?->name ?? 'Walk-in Customer',
            'customer_id' => $order->customer?->id,
            'customerPhone' => $order->customer?->phone ?? '',
            'address' => $order->customer?->address ?? '',
            'shipping_address' => $order->shipping_address,
            'customer' => $order->customer,
            'paymethod' => $this->getPaymentMethod($order),
            'totalorder' => $customerTotalOrders,
            'status' => $this->getStatusLabel($order->status),
            'order_status' => $order->status,
            'note' => $this->resolveDisplayNote($order->note, $order->is_otp_verified),
            'source' => $this->resolveOrderDisplaySource($order),
            'paymentStatus' => $this->getPaymentStatusLabel($order->payment_status),
            'items' => $order->orderDetails->map(function ($detail) {
                $item = [
                    'id' => $detail->id,
                    'title' => $detail->product?->title ?? 'Unknown Product',
                    'product_id' => $detail->product_id,
                    'variation_id' => $detail->variation_id,
                    'sku' => $detail->product?->sku_code ?? '',
                    'price' => (float) $detail->unit_price,
                    'quantity' => $detail->quantity,
                    'discount' => (float) $detail->discount,
                    'tax' => (float) $detail->tax,
                    'total' => (float) $detail->total,
                    'image' => $detail->product?->thumbnail ?? null,
                ];

                // ✅ Add variation data if exists
                if ($detail->variation) {
                    $item['variation'] = [
                        'id' => $detail->variation->id,
                        'sku' => $detail->variation->sku,
                        'attributes' => $detail->variation->attributes->map(function ($attr) {
                            return [
                                'id' => $attr->id,
                                'attribute_group_id' => $attr->attribute_group_id,
                                'attribute_value_id' => $attr->attribute_value_id,
                                'group_name' => $attr->attributeGroup?->name ?? '',
                                'value_name' => $attr->attributeValue?->name ?? '',
                            ];
                        }),
                    ];

                    // Update SKU to use variation SKU if available
                    if ($detail->variation->sku) {
                        $item['sku'] = $detail->variation->sku;
                    }
                } else {
                    $item['variation'] = null;
                }

                return $item;
            }),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) ($order->discount_on_all + $order->coupon_discount),
            'otherCharges' => (float) $order->other_charges,
            'roundOff' => (float) $order->round_off,
            'totalPrice' => (float) $order->grand_total,
            'paidAmount' => (float) $order->payment_amount,
            'notes' => $this->formatOrderNotes($order->orderNotes, $order->is_otp_verified),
            'activities' => $order->actionLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'type' => $log->action_type,
                    'user_name'  => $log->user?->name ?? '',
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            }),
            'payments' => $order->orderPayments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'change_amount' => (float) $payment->change_amount,
                    'reference_no' => $payment->reference_no,
                    'note' => $payment->note,
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $payment->created_at->diffForHumans(),
                ];
            }),
            'customerOrderStats' => [
                'total' => $customerTotalOrders,
                'delivered' => $deliveredCount,
                'returned' => $returnedCount,
                'cancelled' => $cancelledCount,
                'success_ratio' => $successRatio,
            ],
            'orderDate' => $order->order_date,
            'timeAgo' => $order->created_at->diffForHumans(),
            'warehouse' => $order->warehouse?->name ?? '',
            'type' => $order->type,
            'reference_no' => $order->reference_no,
            'is_walk_in' => (bool) $order->is_walk_in,
            'courierInfo' => $this->getCourierInfo($order),
            'courierMethod' => $this->getCourierMethod(),
            'assigned_users' => $assignedUsers,
            'assigned_to' => $order->assigned_to, // Raw assigned_to array (user IDs)
            'source_info' => $order->source_info,
            'ip_address' => $order->type !== 'pos' ? ($order->source_info['ip'] ?? $order->pixel_source_info['ip'] ?? null) : null,
            'device' => is_array($order->source_info) ? ($order->source_info['device'] ?? (isset($order->source_info['user_agent']) ? \App\Helpers\DeviceHelper::detect($order->source_info['user_agent'])['device'] : null)) : null,
            'platform' => is_array($order->source_info) ? ($order->source_info['platform'] ?? (isset($order->source_info['user_agent']) ? \App\Helpers\DeviceHelper::detect($order->source_info['user_agent'])['platform'] : null)) : null,
            'browser' => is_array($order->source_info) ? ($order->source_info['browser'] ?? (isset($order->source_info['user_agent']) ? \App\Helpers\DeviceHelper::detect($order->source_info['user_agent'])['browser'] : null)) : null,
            'network' => is_array($order->source_info) ? ($order->source_info['network'] ?? (isset($order->source_info['ip']) ? \App\Helpers\NetworkHelper::detect($order->source_info['ip'])['network'] : null)) : null,
            'isp' => is_array($order->source_info) ? ($order->source_info['isp'] ?? (isset($order->source_info['ip']) ? \App\Helpers\NetworkHelper::detect($order->source_info['ip'])['isp'] : null)) : null,

        ];
    }
    public function updateStatus(int $id, $paymentStatus = null, $orderStatus = null): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // Store old values for logging


            $paymentStatusMap = [
                'Unpaid' => 0,
                'Partial' => 1,
                'Paid' => 2,
            ];

            // Update Payment Status if provided
            if ($paymentStatus !== null && isset($paymentStatusMap[$paymentStatus])) {
                $oldPaymentStatus = $this->getPaymentStatusLabel($order->payment_status);

                $order->payment_status = $paymentStatusMap[$paymentStatus];
            }

            // Update Order Status if provided
            if ($orderStatus !== null) {
                $oldOrderStatus = $this->getStatusLabel($order->status);
                $newStatus = $this->getStatusLabel($orderStatus);
                $order->status = $orderStatus;
            }

            // Save the order
            $order->save();


            $changes = [];
            if ($paymentStatus && $oldPaymentStatus !== $paymentStatus) {
                $changes[] = "Payment: {$oldPaymentStatus} → {$paymentStatus}";
            }
            if ($orderStatus && $oldOrderStatus !== $orderStatus) {
                $changes[] = "Order: {$oldOrderStatus} → {$newStatus}";
            }

            if (!empty($changes)) {
                LogHelper::statusChanged(
                    'orders',
                    $order->id,
                    $order->company_id,
                    implode(', ', $changes)
                );
            }

            if ($order->customer_id) {
                app(\App\Services\PartyDueService::class)->recalculatePartyDue($order->customer_id);
            }

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $id,
                'changes' => $changes
            ]);

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order status update failed: ' . $e->getMessage());
            throw $e;
        }
    }
    public function changeStatus(int $id, $orderStatus): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            // Update Order Status if provided
            if ($orderStatus !== null) {
                $oldOrderStatus = $this->getStatusLabel($order->status);
                $newStatus = $this->getStatusLabel($orderStatus);
                $order->status = $orderStatus;
            }

            // Save the order
            $order->save();


            $changes = [];

            if ($orderStatus && $oldOrderStatus !== $orderStatus) {
                $changes[] = "Order: {$oldOrderStatus} → {$newStatus}";
            }

            if (!empty($changes)) {
                LogHelper::statusChanged(
                    'orders',
                    $order->id,
                    $order->company_id,
                    implode(', ', $changes)
                );
            }

            if ($order->customer_id) {
                app(\App\Services\PartyDueService::class)->recalculatePartyDue($order->customer_id);
            }

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $id,
                'changes' => $changes
            ]);

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order status update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function resolveDisplayNote(?string $rawNote, ?bool $isOtpVerified): ?string
    {
        if (empty($rawNote)) {
            return null;
        }

        $trimmed = trim($rawNote);
        if ($trimmed === 'Checkout done but not verify') {
            return $isOtpVerified ? 'Verified' : 'Unverified';
        }

        return $trimmed;
    }

    protected function formatOrderNotes($orderNotes, ?bool $isOtpVerified)
    {
        if (!$orderNotes) {
            return collect([]);
        }

        return $orderNotes
            ->filter(function ($note) use ($isOtpVerified) {
                // If order is verified, omit stale unverified notes
                if ($isOtpVerified && in_array(trim((string) $note->note), ['Checkout done but not verify', 'Unverified'])) {
                    return false;
                }
                return true;
            })
            ->map(function ($note) {
                $text = trim((string) $note->note);
                if ($text === 'Checkout done but not verify') {
                    $text = 'Unverified';
                } elseif (preg_match('/OTP verified successfully \((\d+)\)/i', $text, $matches)) {
                    $text = "OTP Verified ({$matches[1]})";
                }

                return [
                    'id' => $note->id,
                    'note' => $text,
                    'type' => $note->type,
                    'created_at' => $note->created_at ? $note->created_at->format('Y-m-d H:i:s') : null,
                    'time_ago' => $note->created_at ? $note->created_at->diffForHumans() : '',
                ];
            })
            ->values();
    }

    protected function resolveOrderDisplaySource(Order $order): string
    {
        // 1. Determine destination / funnel platform
        $isLanding = ($order->type === Order::TYPE_LANDING || $order->type === 'landing' || !empty($order->landing_page_id));
        $isPos = ($order->type === Order::TYPE_POS || $order->type === 'pos');
        $platform = $isPos ? 'POS' : ($isLanding ? 'Landing' : 'Website');

        if ($isPos) {
            return 'POS';
        }

        // 2. Determine marketing / traffic channel
        $sourceInfo = is_array($order->source_info) ? $order->source_info : (is_string($order->source_info) ? json_decode($order->source_info, true) : []);
        $pixelInfo = is_array($order->pixel_source_info) ? $order->pixel_source_info : (is_string($order->pixel_source_info) ? json_decode($order->pixel_source_info, true) : []);
        $merged = array_merge($pixelInfo ?? [], $sourceInfo ?? []);

        $marketingChannel = null;

        // Check explicit order->source first
        $rawSource = strtolower(trim((string) $order->source));
        if (!empty($rawSource) && !in_array($rawSource, ['sales', 'landing', 'website', 'pos'])) {
            if (str_contains($rawSource, 'facebook')) {
                // If source_info explicitly recorded it as Direct/Organic and no fbclid/utm exists, it was falsely marked due to _fbc cookie
                $infoSource = strtolower(trim((string) ($merged['source'] ?? '')));
                $hasRealFbParam = (!empty($merged['utm_source']) && (str_contains(strtolower($merged['utm_source']), 'fb') || str_contains(strtolower($merged['utm_source']), 'facebook')))
                    || (isset($merged['url']) && str_contains($merged['url'], 'fbclid='));

                if ($infoSource === 'direct/organic' && !$hasRealFbParam) {
                    $marketingChannel = null;
                } else {
                    $marketingChannel = 'Facebook';
                }
            } elseif (str_contains($rawSource, 'tiktok')) {
                $marketingChannel = 'TikTok';
            } elseif (str_contains($rawSource, 'google')) {
                $marketingChannel = 'Google';
            } else {
                $marketingChannel = ucfirst($rawSource);
            }
        }

        // Check source_info or pixel_source_info if not determined yet
        if (!$marketingChannel) {
            $infoSource = strtolower(trim((string) ($merged['source'] ?? $merged['utm_source'] ?? '')));
            if (!empty($infoSource) && !in_array($infoSource, ['direct/organic', 'direct', 'sales', 'landing', 'website', 'pos'])) {
                if (str_contains($infoSource, 'facebook')) {
                    $marketingChannel = 'Facebook';
                } elseif (str_contains($infoSource, 'tiktok')) {
                    $marketingChannel = 'TikTok';
                } elseif (str_contains($infoSource, 'google')) {
                    $marketingChannel = 'Google';
                } else {
                    $marketingChannel = ucfirst($infoSource);
                }
            }
        }

        // Check ad click IDs / query parameters in URL
        if (!$marketingChannel) {
            if (isset($merged['url']) && str_contains($merged['url'], 'fbclid=')) {
                $marketingChannel = 'Facebook';
            } elseif (isset($merged['url']) && str_contains($merged['url'], 'ttclid=')) {
                $marketingChannel = 'TikTok';
            } elseif (isset($merged['url']) && str_contains($merged['url'], 'gclid=')) {
                $marketingChannel = 'Google';
            }
        }

        // 3. Format combined output (e.g. "Facebook, Landing" or "Facebook, Website")
        if ($marketingChannel) {
            return "{$marketingChannel}, {$platform}";
        }

        return $platform;
    }
}

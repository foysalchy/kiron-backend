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
                'customer:id,name,phone,address,email',
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

        if (!empty($filters['customer_name'])) {
            $query->whereHas('customer', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['customer_name'] . '%');
            });
        }

        if (!empty($filters['customer_phone'])) {
            $query->whereHas('customer', function ($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['customer_phone'] . '%');
            });
        }

        if (!empty($filters['order_no'])) {
            $query->where('orders.order_no', 'like', '%' . $filters['order_no'] . '%');
        }

        if (!empty($filters['product_name']) || !empty($filters['product_sku'])) {
            $query->whereHas('orderDetails.product', function ($q) use ($filters) {
                if (!empty($filters['product_name'])) {
                    $q->where('title', 'like', '%' . $filters['product_name'] . '%');
                }
                if (!empty($filters['product_sku'])) {
                    $q->where('sku_code', 'like', '%' . $filters['product_sku'] . '%');
                }
            });
        }

        if (!empty($filters['order_date'])) {
            $query->whereDate('orders.order_date', $filters['order_date']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('orders.order_date', [$filters['date_from'], $filters['date_to']]);
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'woo') {
                $query->where('orders.type', 'sales')
                    ->whereJsonContains('orders.source_info->source_name', 'woo');
            } elseif ($filters['type'] === 'sales' || $filters['type'] === 'website') {
                $query->where('orders.type', 'sales')
                    ->whereNull('orders.source_info');
            } else {
                $query->where('orders.type', $filters['type']);
            }
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('orders.warehouse_id', $filters['warehouse_id']);
        }

        // Search functionality
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('orders.order_no', 'like', '%' . $searchTerm . '%')
                    ->orWhere('orders.reference_no', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('customer', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                    });
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
        $phones = $orders->getCollection()
            ->pluck('customer.phone')
            ->filter()
            ->unique()
            ->values();
        $courierHistories = CourierCheckHistory::whereIn('phone', $phones)
            ->get()
            ->keyBy('phone');
        // Transform the data to include calculated fields
        $orders->getCollection()->transform(function ($order) use ($courierHistories) {
            $customerTotalOrders = 1;
            $fakeOrderCount = 0;
            
            $phone = $order->customer?->phone ?? $order->shipping_address['phone'] ?? null;
            
            if ($order->customer_id) {
                $customerTotalOrders = Order::where('customer_id', $order->customer_id)->count();
            }
            
            if ($phone) {
                $fakeOrderCount = Order::where(function($q) use ($phone) {
                    $q->whereHas('customer', function($cQ) use ($phone) {
                        $cQ->where('phone', $phone);
                    })
                    ->orWhere('shipping_address', 'LIKE', '%"phone":"' . $phone . '"%')
                    ->orWhere('shipping_address', 'LIKE', '%"phone": "' . $phone . '"%');
                })
                ->where('status', Status::Fake->value)
                ->where('id', '!=', $order->id)
                ->count();
            }

            // ✅ courier history lookup
            $phone = $order->customer?->phone;
            $courierHistory = $phone ? ($courierHistories[$phone] ?? null) : null;

            return [
                'id' => $order->id,
                'orderNumber' => $order->order_no,
                'customerName' => $order->customer?->name ?? 'Walk-in Customer',
                'customerPhone' => $order->customer?->phone ?? '',
                'customer_id' => $order->customer?->id ?? '',
                'address' => $order->customer?->address ?? '',
                'paymethod' => $this->getPaymentMethod($order),
                'totalorder' => $customerTotalOrders,
                'fake_order_count' => $fakeOrderCount,
                'status' => $this->getStatusLabel($order->status),
                'order_status' => $order->status,
                'paymentStatus' => $this->getPaymentStatusLabel($order->payment_status),
                'source_info' => $order->source_info,
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
                'notes' => $order->orderNotes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'note' => $note->note,
                        'type' => $note->type,
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'time_ago' => $note->created_at->diffForHumans(),
                    ];
                }),
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

    /**
     * Get order by ID with all details
     */
    public function getOrderById($orderId)
    {
        $order = Order::with([
            'customer',
            'warehouse',
            'company',
            'actionLogs.user',
            'orderDetails.product',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
            'orderPayments',
            'orderNotes' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'coupon'
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
            'paymethod' => $this->getPaymentMethod($order),
            'totalorder' => $customerTotalOrders,
            'status' => $this->getStatusLabel($order->status),
            'order_status' => $order->status,
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
            'notes' => $order->orderNotes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'type' => $note->type,
                    'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $note->created_at->diffForHumans(),
                ];
            }),
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
}

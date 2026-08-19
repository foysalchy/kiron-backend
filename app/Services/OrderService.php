<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Order, OrderDetail, OrderPayment, Party, Product, Bin, User};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Notifications\OrderAssignedNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderUnassignedNotification;
use App\Services\Notification\NotificationRecipientResolver;
use App\Services\Notification\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class OrderService
{
    protected ProductService $productService;
    protected CouponService $couponService;

    public function __construct(
        CouponService $couponService,
        ProductService $productService
    ) {
        $this->productService = $productService;
        $this->couponService = $couponService;
    }

    /**
     * Get all orders
     */
    public function getAllOrders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Order::with([
                'warehouse',
                'customer',
                'coupon',
                'orderDetails.product',
                'orderDetails.variation.attributes.attributeGroup',
                'orderDetails.variation.attributes.attributeValue',
                'orderPayments'
            ]);

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('order_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('order_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('order_no', 'like', "%{$filters['search']}%")
                        ->orWhere('reference_no', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'order_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch orders');
        }
    }
    public function getSelectListOrder(string $q = null): Collection
    {
        try {
            return Order::query()
                ->when($q, function ($query) use ($q) {
                    $query->where('order_no', 'like', "%{$q}%");
                })
                ->where('status', Status::Delivered->value)
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get(['id', 'order_no', 'grand_total']);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch orders');
        }
    }


    /**
     * Get order by ID
     */
    public function getOrderById(int $id, $type): Order
    {
        $order = Order::with([
            'warehouse',
            'customer',
            'coupon',
            'orderDetails.product',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
            'orderPayments'
        ])->where('type', $type)->find($id);

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }

    /**
     * Get order Product
     */
    public function orderProducts(int $orderId)
    {
        $order = Order::with([
            'orderDetails.product',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
        ])->find($orderId);

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }
    /**
     * Get hold order list
     */
    public function getHoldOrderList(int $id, $type): Collection
    {
        $order = Order::with([
            'warehouse',
            'customer',
            'coupon',
            'orderDetails.product',
            'orderDetails.variation.attributes.attributeGroup',
            'orderDetails.variation.attributes.attributeValue',
            'orderPayments'
        ])->where('type', $type)
            ->where('warehouse_id', $id)
            ->where('status', Status::Hold->value)
            ->get();

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }

    /**
     * Create POS Order
     */
    public function createPOSOrder(array $data): Order
    {
        $data['type'] = Order::TYPE_POS;
        if ($data['is_walk_in'] && !empty($data['walk_in_customer']['phone'])) {
            $phone = $data['walk_in_customer']['phone'] ?? null;
            $name  = $data['walk_in_customer']['name']  ?? 'Walk-in Customer';

            // Check if customer already exists by phone
            $customer = null;
            if ($phone) {
                $customer = Party::where('type', Party::TYPE_CUSTOMER)->where('phone', $phone)
                    ->first();
            }

            // Create if not found
            if (!$customer) {
                $customer = Party::create([
                    'type' => Party::TYPE_CUSTOMER,
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make('password'),
                ]);
            }

            // Attach to order
            $data['customer_id'] = $customer->id;
        }
        // Create the order
        $order = $this->createOrder($data, true);

        // ── Due amount: add to customer's balance ────────────────────────────
        // Frontend sends: is_due (bool), due_amount (decimal)
        $isDue     = !empty($data['is_due']);
        $dueAmount = isset($data['due_amount']) ? (float) $data['due_amount'] : 0;

        if ($isDue && $dueAmount > 0 && !empty($data['customer_id'])) {
            Party::where('id', $data['customer_id'])->update([
                'due_amount' => DB::raw("due_amount + {$dueAmount}"),
                'balance'    => DB::raw("balance - {$dueAmount}"),
            ]);
        }



        return $order;
    }

    /**
     * Create Sales Order
     */
    public function createSalesOrder(array $data): Order
    {
        $data['type'] = Order::TYPE_SALES;
        return $this->createOrder($data, false);
    }
    public function createLandingOrder(array $data): Order
    {
        $data['type'] = Order::TYPE_LANDING;
        return $this->createOrder($data, false);
    }

    /**
     * Create order (unified logic)
     */
    public function createOrder(array $data, bool $isPOS = false): Order
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            $couponCode = $data['coupon_code'] ?? null;
            $payments = $data['payments'] ?? [];

            unset($data['items'], $data['payments'], $data['coupon_code']);

            $data['order_date'] = $data['order_date'] ?? Carbon::now();

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Apply coupon if provided
            $data['coupon_discount'] = 0;
            $data['coupon_id'] = null;

            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $data['grand_total'],
                    $data['customer_id'] ?? null
                );

                $data['coupon_id'] = $couponResult['coupon_id'];
                $data['coupon_discount'] = $couponResult['discount_amount'];
                $data['grand_total'] -= $data['coupon_discount'];
            }

            // Handle payments
            $totalPaid = 0;
            if (!empty($payments)) {
                $totalPaid = array_sum(array_column($payments, 'amount'));
            }

            $data['payment_amount'] = $totalPaid;
            $data['payment_status'] = $this->determinePaymentStatus($data['grand_total'], $totalPaid);
            if (!isset($data['status'])) {
                $data['status'] = Status::Pending->value;
            }
            // if ($data['payment_status'] == Order::PAYMENT_PAID && $data['status'] != Status::Hold->value) {
            //     $data['status'] = Status::Delivered->value;
            // }
            // Create order
            $order = Order::create($data);
            // Create order details and deduct stock

            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_group_id' => $item['tax_group_id'] ?? null,

                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);

                // Deduct stock using ProductService (for completed/pending orders, not hold)
                if ($order->status !== Status::Hold && $order->status !== Status::Draft) {
                    $this->deductOrderStock($order, $item);
                }
            }
            if ($order->type === Order::TYPE_SALES || $order->type === Order::TYPE_LANDING) {
                $productIds = collect($items)->pluck('product_id')->filter();

                $assignedUsers = Product::whereIn('id', $productIds)
                    ->whereNotNull('assigned_to')
                    ->pluck('assigned_to')
                    ->unique()
                    ->values()
                    ->toArray();

                if (!empty($assignedUsers)) {
                    $order->updateQuietly(['assigned_to' => $assignedUsers]);
                }
            }
            // Create payments
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $payment['amount'],
                        'change_amount' => $payment['amount'] - $data['grand_total'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }
            }

            // Apply coupon usage
            if ($data['coupon_id']) {
                $this->couponService->applyCoupon(
                    $data['coupon_id'],
                    $order->id,
                    $data['subtotal'],
                    $data['coupon_discount'],
                    $data['customer_id'] ?? null
                );
            }
            NotificationService::notify(
                NotificationRecipientResolver::companySuperAdmin($order->company_id),
                new OrderCreatedNotification($order->id, $order->order_no, $order->company_id)
            );
            LogHelper::created('orders', $order->id, $order->company_id, 'total amount ' . $order->grand_total);
            DB::commit();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'type' => $order->type,
                'status' => $order->status
            ]);

            return $order->load([
                'warehouse',
                'customer',
                'coupon',
                'company',
                'orderDetails.product',
                'orderDetails.variation.attributes.attributeGroup',
                'orderDetails.variation.attributes.attributeValue',
                'orderPayments'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create order: ' . $e->getMessage());
        }
    }
    /**
     * Update Order
     */
    public function updateOrder(int $id, array $data, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            // Cannot update delivered or cancelled orders
            if ($order->isDelivered()) {
                throw ApiException::badRequest('Cannot update delivered order');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot update cancelled order');
            }

            $items = $data['items'] ?? [];
            $couponCode = $data['coupon_code'] ?? null;
            $payments = $data['payments'] ?? [];

            unset($data['items'], $data['payments'], $data['coupon_code']);

            // Store old order details for stock restoration
            $oldOrderDetails = $order->orderDetails->toArray();
            $oldStatus = $order->status;
            $oldCouponId = $order->coupon_id;

            // If order was not on hold, restore stock first (we'll deduct new stock later)
            if ($oldStatus != Status::Hold->value) {
                $this->restoreOrderStock($order);
            }

            // Delete old order details and payments
            $order->orderDetails()->delete();
            $order->orderPayments()->delete();

            // Calculate new totals
            if (!empty($items)) {
                $totals = $this->calculateTotals($items, $data);
                $data = array_merge($data, $totals);
            }

            // Handle coupon
            $data['coupon_discount'] = 0;
            $data['coupon_id'] = null;

            // Remove old coupon if changed
            if ($oldCouponId && $oldCouponId != $data['coupon_id']) {
                // Here you might want to reverse the old coupon usage
                // Depends on your CouponService implementation
            }

            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $data['grand_total'],
                    $data['customer_id'] ?? null
                );

                $data['coupon_id'] = $couponResult['coupon_id'];
                $data['coupon_discount'] = $couponResult['discount_amount'];
                $data['grand_total'] -= $data['coupon_discount'];
            }

            // Handle payments
            $totalPaid = 0;
            if (!empty($payments)) {
                $totalPaid = array_sum(array_column($payments, 'amount'));
            }

            $data['payment_amount'] = $totalPaid;
            $data['payment_status'] = $this->determinePaymentStatus($data['grand_total'], $totalPaid);
            $oldDue = max(0, $order->grand_total - $order->payment_amount);

            // Update order
            $order->update($data);
            if ($order->customer_id) {
                $newDue = max(0, $data['grand_total'] - $data['payment_amount']);
                $dueDifference = $newDue - $oldDue;

                if ($dueDifference > 0) {
                    Party::where('id', $order->customer_id)->increment('due_amount', $dueDifference);
                } elseif ($dueDifference < 0) {
                    $reduceAmount = min(abs($dueDifference), Party::where('id', $order->customer_id)->value('due_amount'));
                    Party::where('id', $order->customer_id)->decrement('due_amount', $reduceAmount);
                }
            }
            // Create new order details
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_group_id' => $item['tax_group_id'] ?? null,

                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            // Deduct new stock (only if not on hold)
            if ($order->status != Status::Hold->value && $order->status != Status::Draft->value) {
                foreach ($items as $item) {
                    $this->deductOrderStock($order, $item);
                }
            }

            // Create new payments
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $payment['amount'],
                        'change_amount' => $payment['amount'] - $data['grand_total'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }
            }

            // Apply new coupon usage
            if ($data['coupon_id']) {
                $this->couponService->applyCoupon(
                    $data['coupon_id'],
                    $order->id,
                    $data['subtotal'],
                    $data['coupon_discount'],
                    $data['customer_id'] ?? null
                );
            }

            LogHelper::updated('orders', $order->id, $order->company_id, 'total amount ' . $order->grand_total);
            DB::commit();

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'type' => $order->type,
                'status' => $order->status
            ]);

            return $order->fresh()->load([
                'warehouse',
                'customer',
                'coupon',
                'orderDetails.product',
                'orderDetails.variation.attributes.attributeGroup',
                'orderDetails.variation.attributes.attributeValue',
                'orderPayments'
            ]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update order: ' . $e->getMessage());
        }
    }

    public function updateShiping(int $id, array $data, ?string $type = null): Order
    {
        $order = Order::find($id);

        if (!$order) {
            throw ApiException::notFound('Order not found');
        }

        // shipping_address এলে existing data-র সাথে merge করো, null field গুলো overwrite করো না
        if (isset($data['shipping_address']) && is_array($data['shipping_address'])) {
            $existingAddress = $order->shipping_address ?? [];

            // শুধু যেসব key request-এ পাঠানো হয়েছে এবং value null না, সেগুলোই override হবে
            $newAddress = array_filter(
                $data['shipping_address'],
                fn($value) => !is_null($value)
            );

            $data['shipping_address'] = array_merge($existingAddress, $newAddress);
        }

       
        $order->fill($data);
        $order->save();

        return $order->fresh();
    }
    /**
     * Cancel order
     */
    public function cancelOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if ($order->isDelivered()) {
                throw ApiException::badRequest('Cannot cancel delivered order');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Order is already cancelled');
            }

            $oldStatus = $order->status;

            // If order was pending/completed (not on hold), restore stock
            if ($oldStatus !== Status::Hold->value && $order->status != Status::Draft->value) {
                $this->restoreOrderStock($order);
            }

            $order->update(['status' => Status::Cancelled->value]);

            DB::commit();

            Log::info('Order cancelled', ['order_id' => $id]);
            LogHelper::custom('cancelled', 'order', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel order');
        }
    }

    /**
     * Complete order
     */
    public function completeOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if ($order->isDelivered()) {
                throw ApiException::badRequest('Order is already delivered');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot delivered cancelled order');
            }

            $oldStatus = $order->status;

            // If order was on hold, deduct stock now
            if ($oldStatus == Status::Hold->value && $order->status != Status::Draft->value) {
                foreach ($order->orderDetails as $detail) {
                    $this->deductOrderStock($order, [
                        'product_id' => $detail->product_id,
                        'variation_id' => $detail->variation_id,
                        'quantity' => $detail->quantity
                    ]);
                }
            }

            $order->update(['status' => Status::Delivered->value]);

            DB::commit();

            Log::info('Order delivered', ['order_id' => $id]);
            LogHelper::custom('delivered', 'orders', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order delivery failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to deliver order');
        }
    }
    public function changeStatus(int $id, string $status): Order
    {
        DB::beginTransaction();


        try {
            $order = Order::with([
                'orderDetails.product',
                'orderDetails.variation',
            ])->find($id);
            Log::info("Changing order status for order ID: {$id} to {$status}");

            if (!$order) {
                throw ApiException::notFound('Order not found');
            }

            $getStatus = Status::from($status);
            $oldStatus = $order->status;
            $getOldStatus = Status::from($oldStatus);

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot change cancelled order status');
            }


            $allowedFromDelivered = [
                Status::ReturntoCourier,
                Status::ReturnReceived,
                Status::Returned,
            ];

            if ($order->isDelivered() && !in_array($getStatus, $allowedFromDelivered)) {
                throw ApiException::badRequest(
                    'Order is already delivered. Only return-related status changes are allowed.'
                );
            }
            $wasHoldOrDraft = $oldStatus == Status::Hold->value || $oldStatus == Status::Draft->value;

            if ($getStatus == Status::Cancelled) {
                if (!$wasHoldOrDraft) {
                    $this->restoreOrderStock($order);
                }
            } elseif ($wasHoldOrDraft) {
                // Hold/Draft থেকে অন্য active status-এ গেলে stock deduct করো
                foreach ($order->orderDetails as $detail) {
                    $this->deductOrderStock($order, [
                        'product_id'   => $detail->product_id,
                        'variation_id' => $detail->variation_id,
                        'quantity'     => $detail->quantity,
                    ]);
                }
            }


            $order->update([
                'status' => $getStatus->value
            ]);
            try {
                app(\App\Services\SmsSendService::class)->sendStatusBasedSms($order);
            } catch (\Exception $smsError) {
                Log::warning("Automated SMS failed: " . $smsError->getMessage());
                // এসএমএস না গেলেও অর্ডার আপডেট যাতে হয়ে যায়, তাই ট্রাই-ক্যাচ রাখা হয়েছে
            }

            $logStatus = "{$getOldStatus->label()} → {$getStatus->label()}";

            DB::commit();

            Log::info('Order status changed', [
                'order_id'   => $id,
                'old_status' => $getOldStatus->label(),
                'new_status' => $getStatus->label(),
            ]);

            LogHelper::statusChanged('orders', $order->id, $order->company_id, $logStatus);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status change failed', [
                'order_id' => $id,
                'error'    => $e->getMessage(),
            ]);
            throw ApiException::serverError('Failed to change order status');
        }
    }

    public function deleteOrder(int $id): void
    {
        DB::beginTransaction();

        try {
            $order = Order::with([
                'orderDetails.product',
                'orderDetails.variation',
            ])->find($id);

            if (!$order) {
                throw ApiException::notFound('Order not found');
            }

            $notDeletableStatuses = [
                Status::HandovertoCourier->value,
                Status::InTransit->value,
                Status::Delivered->value,
                Status::ReturntoCourier->value,
                Status::ReturnReceived->value,
                Status::Returned->value,
                Status::Cancelled->value,
            ];

            if (in_array($order->status, $notDeletableStatuses)) {
                throw ApiException::badRequest(
                    'Order cannot be deleted after it has been shipped/handed over to courier.'
                );
            }

            // Draft/Hold ছাড়া বাকি সব status এ stock আগে থেকেই deduct করা আছে, তাই restore করতে হবে
            if (!in_array($order->status, [Status::Draft->value, Status::Hold->value])) {
                $this->restoreOrderStock($order);
            }

            // Related records delete
            $order->orderDetails()->delete();
            $order->orderPayments()->delete();
            $order->orderNotes()->delete();

            $order->delete();

            DB::commit();

            Log::info('Order deleted', [
                'order_id' => $id,
                'status'   => $order->status,
            ]);

            LogHelper::statusChanged('orders', $order->id, $order->company_id, "Order deleted (was: {$order->status})");
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order delete failed', [
                'order_id' => $id,
                'error'    => $e->getMessage(),
            ]);
            throw ApiException::serverError('Failed to delete order');
        }
    }
    public function assignUsers(int $id, array $userIds): array
    {
        $order = Order::findOrFail($id);

        $previousIds = $order->assigned_to ?? [];
        $added       = array_diff($userIds, $previousIds);
        $removed     = array_diff($previousIds, $userIds);

        $order->update(['assigned_to' => $userIds]);

        $parts = [];
        if (!empty($added)) {
            $parts[] = count($added) . ' user' . (count($added) > 1 ? 's' : '') . ' added';
        }
        if (!empty($removed)) {
            $parts[] = count($removed) . ' user' . (count($removed) > 1 ? 's' : '') . ' removed';
        }
        $message = !empty($parts) ? implode(', ', $parts) : 'no changes';

        LogHelper::custom('assigned_users', 'orders', $id, $order->company_id, $message);

        // ── Notifications ──
        $companySuperAdmins = NotificationRecipientResolver::companySuperAdmin($order->company_id);

        if (!empty($added)) {
            $addedUsers = User::whereIn('id', $added)->get();
            $recipients = $companySuperAdmins->concat($addedUsers);

            NotificationService::notify(
                $recipients,
                new OrderAssignedNotification(
                    $order->id,
                    $order->order_no,
                    $order->company_id,
                    $addedUsers->pluck('id')->all(),
                    $addedUsers->pluck('name')->all(),
                )
            );
        }

        if (!empty($removed)) {
            $removedUsers = User::whereIn('id', $removed)->get();
            $recipients = $companySuperAdmins->concat($removedUsers);

            NotificationService::notify(
                $recipients,
                new OrderUnassignedNotification(
                    $order->id,
                    $order->order_no,
                    $order->company_id,
                    $removedUsers->pluck('id')->all(),
                    $removedUsers->pluck('name')->all(),
                )
            );
        }

        return [
            'order_id'    => $order->id,
            'assigned_to' => $userIds,
        ];
    }
    /**
     * Hold order (POS only)
     */
    public function holdOrder(int $id, ?string $ref = null, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if (!$order->isPOS()) {
                throw ApiException::badRequest('Only POS orders can be put on hold');
            }

            if ($order->isDelivered() || $order->isCancelled()) {
                throw ApiException::badRequest('Cannot hold delivered or cancelled order');
            }

            if ($order->isOnHold()) {
                throw ApiException::badRequest('Order is already on hold');
            }

            $oldStatus = $order->status;

            // If order was pending, restore stock (will be deducted when resumed/completed)
            if ($oldStatus == Status::Pending->value) {
                $this->restoreOrderStock($order);
            }

            $order->update([
                'status' => Status::Hold->value,
                'hold_ref' => $ref,
            ]);

            DB::commit();

            Log::info('Order put on hold', ['order_id' => $id]);
            LogHelper::custom('on_hold', 'orders', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order hold failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to hold order');
        }
    }

    /**
     * Resume held order
     */
    public function resumeOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);



            // Deduct stock when resuming
            foreach ($order->orderDetails as $detail) {
                $this->deductOrderStock($order, [
                    'product_id' => $detail->product_id,
                    'variation_id' => $detail->variation_id,
                    'quantity' => $detail->quantity
                ]);
            }

            $order->update([
                'status' => Status::Resumed->value,
                'hold_ref' => null,
            ]);

            DB::commit();

            Log::info('Order resumed from hold', ['order_id' => $id]);
            LogHelper::custom('resumed', 'orders', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order resume failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to resume order');
        }
    }
    /**
     * add payment
     */
    public function addPayment(int $id, $data): Order
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            if ($order->isPaid()) {
                throw ApiException::badRequest('Already paid');
            }
            $totalPaid = $order->payment_amount + $data['amount'];
            $payment_status = $this->determinePaymentStatus($order->grand_total, $totalPaid);

            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_no' => $data['reference_no'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
            $order->update([
                'payment_amount' => $totalPaid,
                'payment_status' => $payment_status,
            ]);

            $customer = $order->customer;
            if ($customer && $data['amount'] > 0) {
                // If due amount was added to customer balance during order creation, reduce it now

                $reduceAmount = min($data['amount'], $customer->due_amount);
                Party::where('id', $customer->id)
                    ->decrement('due_amount', $reduceAmount);
            }
            DB::commit();

            Log::info('Order payment submit', ['order_id' => $id]);
            LogHelper::custom('payment', 'orders', $id, $order->company_id, 'paid amount. ' . $data['amount']);

            return $order;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order resume failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to resume order');
        }
    }

    /**
     * Deduct stock for order item
     */
    private function deductOrderStock(Order $order, array $item): void
    {
        Log::info('Deducting stock for order item', [
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'variation_id' => $item['variation_id'] ?? null,
            'quantity' => $item['quantity']
        ]);
        $warehouseId = $order->warehouse_id ?? ($item['warehouse_id'] ?? null);
        if (!$warehouseId) {
            Log::warning("Skipping stock deduction: Warehouse ID is missing for Order #{$order->id}");
            return;
        }


        if (!empty($item['bin_id'])) {
            $warehouseId = Bin::find($item['bin_id'])->warehouse_id ?? $warehouseId;
        }

        $stockData = [
            'warehouse_id' => $warehouseId,
            'bin_id' => $item['bin_id'] ?? null,
            'quantity' => $item['quantity'],
            'batch_number' => null,
            'serial_numbers' => null,
            'transaction_type' => 'sale',
            // 'reference_type' => $order->type === Order::TYPE_POS ? 'POSOrder' : 'SalesOrder',
            'reference_type' => match ($order->type) {
                Order::TYPE_POS => 'POSOrder',
                Order::TYPE_LANDING => 'LandingOrder',
                default => 'SalesOrder',
            },
            'reference_id' => $order->id,
            'notes' => "Stock deducted for order: {$order->order_no}"
        ];
        Log::info('Prepared stock data for deduction', $stockData);
        if (isset($item['variation_id']) && $item['variation_id']) {
            $stockData['variation_id'] = $item['variation_id'];
        }
        // dd($stockData);

        $this->productService->removeStockFromWarehouse($item['product_id'], $stockData);
    }

    /**
     * Restore stock for cancelled/held order
     */
    private function restoreOrderStock(Order $order): void
    {
        foreach ($order->orderDetails as $detail) {
            $stockData = [
                'warehouse_id' => $order->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'return',
                'reference_type' => 'OrderCancellation',
                'reference_id' => $order->id,
                'notes' => "Stock restored from cancelled/held order: {$order->order_no}"
            ];

            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->addStockToWarehouse($detail->product_id, $stockData);
        }
    }


    // CALCULATION METHODS

    /**
     * Calculate item total
     */
    private function calculateItemTotal(array $item): float
    {
        $quantity = $item['quantity'];
        $unitPrice = $item['unit_price'];
        $discount = $item['discount'] ?? 0;
        $tax = $item['tax'] ?? 0;

        $total = ($quantity * $unitPrice) - $discount + $tax;

        return round($total, 2);
    }

    /**
     * Calculate order totals
     */
    private function calculateTotals(array $items, array $data): array
    {
        $totalQuantities = 0;
        $subtotal = 0;

        foreach ($items as $item) {
            $totalQuantities += $item['quantity'];
            $subtotal += $this->calculateItemTotal($item);
        }

        $otherCharges = $data['other_charges'] ?? 0;
        $discountOnAll = $data['discount_on_all'] ?? 0;
        $roundOff = $data['round_off'] ?? 0;

        $grandTotal = $subtotal + $otherCharges - $discountOnAll + $roundOff;

        return [
            'total_quantities' => $totalQuantities,
            'subtotal' => round($subtotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Determine payment status
     */
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

    public function getPosDashboardData(array $filters): array
    {
        $range = $filters['range'] ?? 'today';
        $start = $filters['start'] ?? null;
        $end = $filters['end'] ?? null;

        // Determine date range
        [$startDate, $endDate, $previousStart, $previousEnd] = $this->calculateDateRanges($range, $start, $end);

        // Current period stats
        $currentOrders = Order::where('type', 'pos')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalSales = $currentOrders->sum('grand_total');
        $totalOrders = $currentOrders->count();
        $totalCustomers = $currentOrders->whereNotNull('customer_id')->unique('customer_id')->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Previous period stats for growth calculation
        $previousOrders = Order::where('type', 'pos')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->get();

        $previousSales = $previousOrders->sum('grand_total');
        $previousOrderCount = $previousOrders->count();

        $salesGrowth = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;
        $ordersGrowth = $previousOrderCount > 0 ? (($totalOrders - $previousOrderCount) / $previousOrderCount) * 100 : 0;

        return [
            'stats' => [
                'totalSales' => round($totalSales, 2),
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'averageOrderValue' => round($averageOrderValue, 2),
                'salesGrowth' => round($salesGrowth, 2),
                'ordersGrowth' => round($ordersGrowth, 2),
            ],
            'salesChart' => $this->getSalesChartData($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'recentOrders' => $this->getRecentOrders($startDate, $endDate),
            'paymentMethods' => $this->getPaymentMethodsBreakdown($startDate, $endDate),
        ];
    }

    private function calculateDateRanges(string $range, ?string $start, ?string $end): array
    {
        switch ($range) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $previousStart = Carbon::yesterday();
                $previousEnd = Carbon::yesterday()->endOfDay();
                break;

            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                $previousStart = Carbon::yesterday()->subDay();
                $previousEnd = Carbon::yesterday()->subDay()->endOfDay();
                break;

            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $previousStart = Carbon::now()->subWeek()->startOfWeek();
                $previousEnd = Carbon::now()->subWeek()->endOfWeek();
                break;

            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $previousStart = Carbon::now()->subMonth()->startOfMonth();
                $previousEnd = Carbon::now()->subMonth()->endOfMonth();
                break;

            case 'custom':
                $startDate = Carbon::parse($start);
                $endDate = Carbon::parse($end)->endOfDay();
                $diff = $startDate->diffInDays($endDate);
                $previousStart = $startDate->copy()->subDays($diff);
                $previousEnd = $endDate->copy()->subDays($diff);
                break;

            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $previousStart = Carbon::yesterday();
                $previousEnd = Carbon::yesterday()->endOfDay();
        }

        return [$startDate, $endDate, $previousStart, $previousEnd];
    }

    private function getSalesChartData(Carbon $startDate, Carbon $endDate): array
    {
        $salesChart = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $daySales = Order::where('type', 'pos')
                ->whereDate('created_at', $date)
                ->sum('grand_total');

            $dayOrders = Order::where('type', 'pos')
                ->whereDate('created_at', $date)
                ->count();

            $salesChart[] = [
                'date' => $date->format('M d'),
                'sales' => round($daySales, 2),
                'orders' => $dayOrders,
            ];
        }

        return $salesChart;
    }

    private function getTopProducts(Carbon $startDate, Carbon $endDate): array
    {
        return OrderDetail::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('type', 'pos')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->select('product_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(total) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->title ?? 'Product',
                    'quantity' => $item->quantity,
                    'revenue' => round($item->revenue, 2),
                ];
            })
            ->toArray();
    }

    private function getRecentOrders(Carbon $startDate, Carbon $endDate): array
    {
        return Order::where('type', 'pos')
            ->with('customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name ?? null,
                    'grand_total' => $order->grand_total,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                ];
            })
            ->toArray();
    }

    private function getPaymentMethodsBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        return OrderPayment::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('type', 'pos')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->select('payment_method as name', DB::raw('SUM(amount) as value'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst(str_replace('_', ' ', $item->name)),
                    'value' => round($item->value, 2),
                ];
            })
            ->toArray();
    }
}

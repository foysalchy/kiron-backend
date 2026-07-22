<?php

namespace App\Notifications;

class OrderCreatedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $orderId,
        protected string $orderNumber,
        protected int $companyId,
    ) {}

    public function toArray($notifiable): array
    {
        return [
            'type'        => 'order_created',
            'title'       => "New order #{$this->orderNumber} created",
            'message'     => "Order #{$this->orderNumber} has been placed.",
            'action_url'  => "/orders/details/{$this->orderId}",
            'model_type'  => 'Order',
            'model_id'    => $this->orderId,
            'company_id'  => $this->companyId,
        ];
    }
}

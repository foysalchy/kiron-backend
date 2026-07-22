<?php

namespace App\Notifications;

class OrderAssignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $orderId,
        protected string $orderNumber,
        protected int $companyId,
        protected array $assignedUserIds,   
        protected array $assignedUserNames, 
    ) {}

    public function toArray($notifiable): array
    {
        $isAssignedUser = in_array($notifiable->id, $this->assignedUserIds);

        $message = $isAssignedUser
            ? "You have been assigned to order #{$this->orderNumber}."
            : $this->buildAdminMessage();

        return [
            'type'       => 'order_assigned',
            'title'      => "Order #{$this->orderNumber} assigned",
            'message'    => $message,
            'action_url' => "/orders/details/{$this->orderId}",
            'model_type' => 'Order',
            'model_id'   => $this->orderId,
            'company_id' => $this->companyId,
        ];
    }

    protected function buildAdminMessage(): string
    {
        $names = implode(', ', $this->assignedUserNames);
        $count = count($this->assignedUserNames);
        $label = $count > 1 ? 'users' : 'user';

        return "Order #{$this->orderNumber} assigned to {$label}: {$names}.";
    }
}

<?php
namespace App\Notifications;

class OrderUnassignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $orderId,
        protected string $orderNumber,
        protected int $companyId,
        protected array $removedUserIds,
        protected array $removedUserNames,
    ) {}

    public function toArray($notifiable): array
    {
        $isRemovedUser = in_array($notifiable->id, $this->removedUserIds);

        $message = $isRemovedUser
            ? "You have been removed from order #{$this->orderNumber}."
            : $this->buildAdminMessage();

        return [
            'type'       => 'order_unassigned',
            'title'      => "Order #{$this->orderNumber} unassigned",
            'message'    => $message,
            'action_url' => "#",
            'model_type' => 'Order',
            'model_id'   => $this->orderId,
            'company_id' => $this->companyId,
        ];
    }

    protected function buildAdminMessage(): string
    {
        $names = implode(', ', $this->removedUserNames);
        $count = count($this->removedUserNames);
        $label = $count > 1 ? 'users' : 'user';

        return "Order #{$this->orderNumber} unassigned from {$label}: {$names}.";
    }
}

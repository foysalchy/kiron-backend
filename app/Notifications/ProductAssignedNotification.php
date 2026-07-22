<?php

namespace App\Notifications;

class ProductAssignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $productId,
        protected string $productName,
        protected ?int $companyId,
        protected int $assignedUserId,
    ) {}

    public function toArray($notifiable): array
    {
        $isAssignedUser = $notifiable->id === $this->assignedUserId;

        $message = $isAssignedUser
            ? "You have been assigned to product \"{$this->productName}\"."
            : "Product \"{$this->productName}\" was assigned to a user.";

        return [
            'type'       => 'product_assigned',
            'title'      => "Product assigned: {$this->productName}",
            'message'    => $message,
            'action_url' => "/products",
            'model_type' => 'Product',
            'model_id'   => $this->productId,
            'company_id' => $this->companyId,
        ];
    }
}

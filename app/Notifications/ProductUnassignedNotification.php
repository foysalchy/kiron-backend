<?php

// app/Notifications/ProductUnassignedNotification.php
namespace App\Notifications;

class ProductUnassignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $productId,
        protected string $productName,
        protected ?int $companyId,
        protected int $removedUserId,
    ) {}

    public function toArray($notifiable): array
    {
        $isRemovedUser = $notifiable->id === $this->removedUserId;

        $message = $isRemovedUser
            ? "You have been removed from product \"{$this->productName}\"."
            : "A user was removed from product \"{$this->productName}\".";

        return [
            'type'       => 'product_unassigned',
            'title'      => "Product unassigned: {$this->productName}",
            'message'    => $message,
            'action_url' => "#",
            'model_type' => 'Product',
            'model_id'   => $this->productId,
            'company_id' => $this->companyId,
        ];
    }
}

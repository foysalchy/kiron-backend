<?php

// app/Notifications/OmnichannelAssignedNotification.php
namespace App\Notifications;

class OmnichannelAssignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $conversationId,
        protected ?int $companyId,
        protected int $assignedUserId,
        protected string $actorName,
    ) {}

    public function toArray($notifiable): array
    {
        $isAssignedUser = $notifiable->id === $this->assignedUserId;

        $message = $isAssignedUser
            ? "You have been assigned to a conversation."
            : "{$this->actorName} assigned a conversation to a user.";

        return [
            'type'       => 'omnichannel_assigned',
            'title'      => "Conversation assigned",
            'message'    => $message,
            'action_url' => "/omni",
            'model_type' => 'Conversation',
            'model_id'   => $this->conversationId,
            'company_id' => $this->companyId,
        ];
    }
}

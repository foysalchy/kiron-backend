<?php
namespace App\Notifications;

class OmnichannelUnassignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $conversationId,
        protected ?int $companyId,
        protected int $removedUserId,
    ) {}

    public function toArray($notifiable): array
    {
        $isRemovedUser = $notifiable->id === $this->removedUserId;

        $message = $isRemovedUser
            ? "You have been removed from a conversation."
            : "A user was removed from a conversation.";

        return [
            'type'       => 'omnichannel_unassigned',
            'title'      => "Conversation unassigned",
            'message'    => $message,
            'action_url' => "/omni",
            'model_type' => 'Conversation',
            'model_id'   => $this->conversationId,
            'company_id' => $this->companyId,
        ];
    }
}
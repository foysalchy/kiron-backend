<?php
namespace App\Notifications;

class TicketCreatedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $ticketId,
        protected string $subject,
        protected string $companyName,
        protected string $userName,
    ) {}

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'ticket_created',
            'title'      => "New support ticket: {$this->subject}",
            'message'    => "{$this->userName} from {$this->companyName} created a ticket: \"{$this->subject}\".",
            'action_url' => "/support-tickets",
            'model_type' => 'SupportTicket',
            'model_id'   => $this->ticketId,
        ];
    }
}

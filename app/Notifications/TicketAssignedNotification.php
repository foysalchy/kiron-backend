<?php

// app/Notifications/TicketAssignedNotification.php
namespace App\Notifications;

class TicketAssignedNotification extends BaseAppNotification
{
    public function __construct(
        protected int $ticketId,
        protected string $subject,
        protected ?int $companyId,   // nullable kora holo
        protected int $assignedUserId,
        protected string $assignedUserName,
        protected string $actorName,
    ) {}

    public function toArray($notifiable): array
    {
        $isAssignedUser = $notifiable->id === $this->assignedUserId;

        $message = $isAssignedUser
            ? "You have been assigned to ticket: \"{$this->subject}\"."
            : "{$this->actorName} assigned {$this->assignedUserName} to ticket: \"{$this->subject}\".";

        return [
            'type'       => 'ticket_assigned',
            'title'      => "Ticket assigned: {$this->subject}",
            'message'    => $message,
            'action_url' => "/support-tickets",
            'model_type' => 'SupportTicket',
            'model_id'   => $this->ticketId,
            'company_id' => $this->companyId,
        ];
    }
}

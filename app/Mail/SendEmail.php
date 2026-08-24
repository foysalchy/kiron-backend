<?php

namespace App\Mail;

use App\Services\TenantMailConfigurator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public string $bodyContent,
        public ?int $companyId = null,
        public ?string $companyName = null,
        public ?string $companyLogo = null,
        public ?string $supportEmail = null,
        public ?array $socials =[],
    ) {
        //
    }

    public function envelope(): Envelope
    {

        if ($this->companyId) {
            TenantMailConfigurator::applyForCompany($this->companyId);
        }
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sendEmail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

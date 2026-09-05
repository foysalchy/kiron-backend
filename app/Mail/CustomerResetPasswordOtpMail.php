<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerResetPasswordOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $recipientEmail,
        public string $otp,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Reset Code',
            to: [$this->recipientEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-reset-password-otp',
            with: [
                'name' => $this->recipientName,
                'otp'  => $this->otp,
            ],
        );
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyOtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $companyName,
        public readonly string $otp,
        public readonly string $type  // 'company' | 'user'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->type === 'company'
            ? 'Your Company Email Verification Code'
            : 'Your Admin Account Verification Code';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-otp',
            with: [
                'companyName' => $this->companyName,
                'otp'         => $this->otp,
                'type'        => $this->type,
            ]
        );
    }
}
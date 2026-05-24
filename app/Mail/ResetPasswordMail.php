<?php

// app/Mail/ResetPasswordMail.php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
        );
    }

    public function content(): Content
    {
        $resetUrl = config('app.frontend_url')
            . '/reset-password'
            . '?token=' . urlencode($this->token)
            . '&email=' . urlencode($this->user->email);

        return new Content(
            view: 'emails.reset-password',
            with: [
                'user'     => $this->user,
                'resetUrl' => $resetUrl,
                'expiry'   => 60,
            ],
        );
    }
}
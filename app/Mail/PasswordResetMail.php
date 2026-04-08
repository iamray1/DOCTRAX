<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(
        public User $user,
        string $rawToken
    ) {
        $this->resetUrl = url('/reset-password?token=' . $rawToken . '&email=' . urlencode($user->email));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password — DepEd DOCTRAX',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }
}

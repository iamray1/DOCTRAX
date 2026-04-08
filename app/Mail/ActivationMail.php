<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $activationUrl;

    public function __construct(
        public User $user,
        string $rawToken
    ) {
        $this->activationUrl = url('/activate/' . $rawToken);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Your Password — DocTrax Account Activation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation',
        );
    }
}

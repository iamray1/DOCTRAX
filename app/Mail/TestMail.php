<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $appName,
        public string $appUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laravel Mail Test - ' . $this->appName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test-mail',
        );
    }
}

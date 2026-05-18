<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $headline;
    public string $messageText;
    public string $changedAt;

    public function __construct(
        public User $user,
        public string $updateType,
        public ?string $oldEmail = null,
        public ?string $newEmail = null
    ) {
        $this->changedAt = now()->setTimezone('Asia/Manila')->format('M d, Y h:i A') . ' PHT';

        if ($updateType === 'email_changed') {
            $this->headline = 'Your account email address was changed';
            $this->messageText = 'We are informing you that your DocTrax account email address was updated.';
            return;
        }

        $this->headline = 'Your account password was changed';
        $this->messageText = 'We are informing you that your DocTrax account password was updated.';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->updateType === 'email_changed'
                ? 'DocTrax Account Email Changed'
                : 'DocTrax Account Password Changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-update',
        );
    }
}

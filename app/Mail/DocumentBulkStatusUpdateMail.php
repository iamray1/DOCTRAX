<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DocumentBulkStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    private const STATUS_COPY = [
        'for_pickup' => [
            'subject' => 'DocTrax Update: Documents Ready for Pickup',
            'label' => 'For Pickup',
            'headline' => 'Your documents are ready for pickup',
            'body' => 'The following documents have been marked For Pickup. Please coordinate with the releasing office before claiming them.',
        ],
        'returned' => [
            'subject' => 'DocTrax Update: Documents For Return',
            'label' => 'For Return',
            'headline' => 'Your documents are ready for return',
            'body' => 'The following documents have been marked For Return. Please coordinate with the releasing office to claim them.',
        ],
        'completed' => [
            'subject' => 'DocTrax Update: Documents Completed',
            'label' => 'Completed',
            'headline' => 'Your document transactions are completed',
            'body' => 'The following documents have been marked Completed. These transactions are now recorded as completed in DocTrax.',
        ],
    ];

    public Collection $documents;
    public string $recipientName;
    public string $status;
    public string $statusLabel;
    public string $headline;
    public string $bodyText;
    public string $documentsUrl;
    public int $documentCount;

    public function __construct($documents, string $status, string $recipientName)
    {
        $this->documents = collect($documents)
            ->values()
            ->each(fn (Document $document) => $document->loadMissing(['currentOffice', 'submittedToOffice']));

        $copy = self::STATUS_COPY[$status] ?? [
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'headline' => 'Your documents were updated',
            'body' => 'The following documents were updated in DocTrax.',
        ];

        $hasAccountDocument = $this->documents->contains(fn (Document $document) => ! empty($document->user_id));

        $this->recipientName = trim($recipientName) !== '' ? trim($recipientName) : 'there';
        $this->status = $status;
        $this->statusLabel = $copy['label'];
        $this->headline = $copy['headline'];
        $this->bodyText = $copy['body'];
        $this->documentsUrl = $hasAccountDocument ? url('/my-documents') : url('/track');
        $this->documentCount = $this->documents->count();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-bulk-status-update',
        );
    }

    private function subjectLine(): string
    {
        $copy = self::STATUS_COPY[$this->status] ?? [
            'subject' => 'DocTrax Document Status Update',
        ];

        return $copy['subject'] . ' - ' . $this->documentCount . ' Documents';
    }
}

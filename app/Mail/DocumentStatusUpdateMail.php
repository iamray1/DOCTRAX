<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private const STATUS_COPY = [
        'for_pickup' => [
            'subject' => 'DocTrax Update: Document Ready for Pickup',
            'label' => 'For Pickup',
            'headline' => 'Your document is ready for pickup',
            'body' => 'Your document has been marked For Pickup. Please coordinate with the releasing office before claiming it.',
        ],
        'returned' => [
            'subject' => 'DocTrax Update: Document For Return',
            'label' => 'For Return',
            'headline' => 'Your document is ready for return',
            'body' => 'Your document has been marked For Return. Please coordinate with the releasing office to claim it.',
        ],
        'completed' => [
            'subject' => 'DocTrax Update: Submitted Document Done',
            'label' => 'Completed',
            'headline' => 'Your submitted document is done',
            'body' => 'The document you submitted has been marked Completed. This transaction is now recorded as done in DocTrax.',
        ],
    ];

    public string $recipientName;
    public string $statusLabel;
    public string $headline;
    public string $bodyText;
    public string $referenceNumber;
    public string $trackingNumber;
    public string $documentTitle;
    public string $officeName;
    public string $updatedAt;
    public string $trackUrl;

    public function __construct(
        public Document $document,
        string $recipientName
    ) {
        $this->document->loadMissing(['currentOffice', 'submittedToOffice']);

        $copy = self::STATUS_COPY[(string) $this->document->status] ?? [
            'label' => $this->document->statusLabel(),
            'headline' => 'Your document status was updated',
            'body' => 'Your document status was updated in DocTrax.',
        ];

        $lookup = $this->document->reference_number ?: $this->document->tracking_number;
        $updatedAt = $this->document->last_action_at ?? $this->document->updated_at ?? now();

        $this->recipientName = trim($recipientName) !== '' ? trim($recipientName) : 'there';
        $this->statusLabel = $copy['label'];
        $this->headline = $copy['headline'];
        $this->bodyText = $copy['body'];
        $this->referenceNumber = $this->document->reference_number ?: 'N/A';
        $this->trackingNumber = $this->document->tracking_number ?: 'N/A';
        $this->documentTitle = trim((string) $this->document->subject) !== ''
            ? (string) $this->document->subject
            : 'Submitted Document';
        $this->officeName = $this->document->currentOffice?->name
            ?: ($this->document->submittedToOffice?->name ?: 'Releasing Office');
        $this->updatedAt = $updatedAt->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') . ' PHT';
        $this->trackUrl = $lookup
            ? url('/track?ref=' . urlencode((string) $lookup))
            : url('/track');
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
            view: 'emails.document-status-update',
        );
    }

    private function subjectLine(): string
    {
        $copy = self::STATUS_COPY[(string) $this->document->status] ?? [
            'subject' => 'DocTrax Document Status Update',
        ];

        $lookup = $this->document->reference_number ?: $this->document->tracking_number;

        if ((string) $this->document->status === 'completed' && trim((string) $this->document->subject) !== '') {
            return $copy['subject'] . ' - ' . $this->document->subject;
        }

        return $lookup ? $copy['subject'] . ' - ' . $lookup : $copy['subject'];
    }
}

<?php

namespace App\Services;

use App\Mail\DocumentBulkStatusUpdateMail;
use App\Mail\DocumentStatusUpdateMail;
use App\Models\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DocumentStatusEmailService
{
    private const EMAIL_STATUSES = [
        'for_pickup',
        'returned',
        'completed',
    ];

    private static int $bulkCaptureDepth = 0;

    /**
     * @var array<int, string>
     */
    private static array $capturedStatusChanges = [];

    public static function shouldSendForStatus(?string $status): bool
    {
        return in_array($status, self::EMAIL_STATUSES, true);
    }

    public static function sendAfterResponse(callable $callback): void
    {
        try {
            app()->terminating(function () use ($callback): void {
                $callback();
            });
        } catch (Throwable $exception) {
            $callback();
        }
    }

    public static function beginBulkEmailCapture(): void
    {
        self::$bulkCaptureDepth++;
    }

    public static function captureModelEmail(Document $document): bool
    {
        if (self::$bulkCaptureDepth < 1) {
            return false;
        }

        self::$capturedStatusChanges[(int) $document->id] = (string) $document->status;

        return true;
    }

    public static function endBulkEmailCaptureAndSend(): void
    {
        if (self::$bulkCaptureDepth > 0) {
            self::$bulkCaptureDepth--;
        }

        if (self::$bulkCaptureDepth > 0) {
            return;
        }

        $changes = self::$capturedStatusChanges;
        self::$capturedStatusChanges = [];

        if ($changes !== []) {
            self::sendAfterResponse(function () use ($changes): void {
                app(self::class)->sendBulkStatusChanges($changes);
            });
        }
    }

    public function send(Document $document): void
    {
        $status = (string) $document->status;
        $recipient = null;

        try {
            if (! self::shouldSendForStatus($status)) {
                return;
            }

            $document->loadMissing(['user', 'currentOffice', 'submittedToOffice']);
            $recipient = $this->recipientEmail($document);

            if ($recipient === null) {
                return;
            }

            Mail::to($recipient)->send(
                new DocumentStatusUpdateMail($document, $this->recipientName($document))
            );
        } catch (Throwable $exception) {
            Log::warning('Document status email failed.', [
                'document_id' => $document->id,
                'status' => $status,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param array<int, string> $statusByDocumentId
     */
    public function sendBulkStatusChanges(array $statusByDocumentId): void
    {
        if ($statusByDocumentId === []) {
            return;
        }

        $documents = Document::with(['user', 'currentOffice', 'submittedToOffice'])
            ->whereIn('id', array_keys($statusByDocumentId))
            ->get()
            ->filter(function (Document $document) use ($statusByDocumentId) {
                $status = (string) $document->status;

                return self::shouldSendForStatus($status)
                    && ($statusByDocumentId[(int) $document->id] ?? null) === $status
                    && $this->recipientEmail($document) !== null;
            });

        $documents
            ->groupBy(fn (Document $document) => $this->recipientEmail($document) . '|' . $document->status)
            ->each(function ($recipientDocuments): void {
                $recipientDocuments = $recipientDocuments->values();

                if ($recipientDocuments->count() > 1) {
                    $this->sendBulkGroup($recipientDocuments);
                    return;
                }

                $recipientDocuments->each(fn (Document $document) => $this->send($document));
            });
    }

    private function sendBulkGroup($documents): void
    {
        $firstDocument = $documents->first();
        $recipient = $this->recipientEmail($firstDocument);

        try {
            if ($recipient === null) {
                return;
            }

            Mail::to($recipient)->send(
                new DocumentBulkStatusUpdateMail($documents, (string) $firstDocument->status, $this->recipientName($firstDocument))
            );
        } catch (Throwable $exception) {
            Log::warning('Bulk document status email failed.', [
                'document_ids' => $documents->pluck('id')->all(),
                'status' => (string) $firstDocument->status,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function recipientEmail(Document $document): ?string
    {
        foreach ([$document->user?->email, $document->sender_email] as $candidate) {
            $email = strtolower(trim((string) $candidate));

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    private function recipientName(Document $document): string
    {
        $name = trim((string) ($document->user?->name ?: $document->sender_name));

        return $name !== '' ? $name : 'there';
    }
}

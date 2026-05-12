<?php

namespace App\Models;

use App\Services\DocumentStatusEmailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    /**
     * Clear stats caches when any document is created, updated, or deleted.
     * Keeps cached stats endpoints in sync with the database.
     */
    protected static function booted(): void
    {
        $bustCache = function (Document $doc) {
            // Global caches
            Cache::forget('admin_stats');
            Cache::forget('records_stats');

            // Per-user cache (submitter)
            foreach (array_unique(array_filter([$doc->user_id, $doc->getOriginal('user_id')])) as $userId) {
                Cache::forget('user_stats_' . $userId);
            }

            // Per-office cache (current office)
            $officeIds = array_unique(array_filter([
                $doc->current_office_id,
                $doc->getOriginal('current_office_id'),
                $doc->submitted_to_office_id,
                $doc->getOriginal('submitted_to_office_id'),
            ]));

            foreach ($officeIds as $officeId) {
                Cache::forget('office_stats_' . $officeId);
            }

            $handlerIds = array_unique(array_filter([
                $doc->current_handler_id,
                $doc->getOriginal('current_handler_id'),
            ]));

            foreach ($handlerIds as $handlerId) {
                Cache::forget('ict_stats_' . $handlerId);

                foreach ($officeIds as $officeId) {
                    Cache::forget('office_stats_user_' . $officeId . '_' . $handlerId);
                    Cache::forget('ict_stats_' . $handlerId . '_office_' . $officeId);
                }
            }
        };

        static::created($bustCache);
        static::updated($bustCache);
        static::deleted($bustCache);

        static::updated(function (Document $doc): void {
            if (! $doc->wasChanged('status') || ! DocumentStatusEmailService::shouldSendForStatus((string) $doc->status)) {
                return;
            }

            if (DocumentStatusEmailService::captureModelEmail($doc)) {
                return;
            }

            $documentId = (int) $doc->id;
            $status = (string) $doc->status;

            $sendStatusEmail = function () use ($documentId, $status): void {
                try {
                    $freshDocument = Document::with(['user', 'currentOffice', 'submittedToOffice'])->find($documentId);

                    if ($freshDocument && (string) $freshDocument->status === $status) {
                        app(DocumentStatusEmailService::class)->send($freshDocument);
                    }
                } catch (\Throwable $exception) {
                    report($exception);
                }
            };

            try {
                DB::afterCommit(fn () => DocumentStatusEmailService::sendAfterResponse($sendStatusEmail));
            } catch (\Throwable $exception) {
                DocumentStatusEmailService::sendAfterResponse($sendStatusEmail);
            }
        });
    }

    protected $fillable = [
        'submitted_to_office_id',
        'subject',
        'type',
        'sender_name',
        'sender_contact',
        'sender_email',
        'sender_office',
        'recipient_office',
        'description',
    ];

    /**
     * Attributes guarded from mass assignment.
     * tracking_number, reference_number, user_id, status, current_office_id,
     * current_handler_id, last_action_at, archived_at are set explicitly.
     */
    protected $guarded = [
        'id',
        'tracking_number',
        'reference_number',
        'user_id',
        'current_office_id',
        'current_handler_id',
        'status',
        'last_action_at',
        'archived_at',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
        'archived_at'    => 'datetime',
    ];

    // ─── Status constants ───
    const STATUSES = [
        'submitted'  => 'Submitted',
        'received'   => 'Received',
        'in_review'  => 'Processing',
        'on_hold'    => 'On Hold',
        'completed'  => 'Completed',
        'for_pickup' => 'For Pickup',
        'returned'   => 'For Return',
        'cancelled'  => 'Cancelled',
        'archived'   => 'Archived',
    ];

    // Statuses exposed in document search/filter dropdowns.
    // Keep legacy/internal statuses in STATUSES so existing records still render correctly.
    const FILTER_STATUSES = [
        'submitted'  => 'Submitted',
        'received'   => 'Received',
        'in_review'  => 'Processing',
        'completed'  => 'Completed',
        'for_pickup' => 'For Pickup',
        'returned'   => 'For Return',
        'archived'   => 'Archived',
    ];

    const STATUS_COLORS = [
        'submitted'  => '#c2410c',
        'received'   => '#c2410c',
        'in_review'  => '#c2410c',
        'on_hold'    => '#c2410c',
        'completed'  => '#c2410c',
        'for_pickup' => '#c2410c',
        'returned'   => '#c2410c',
        'cancelled'  => '#c2410c',
        'archived'   => '#c2410c',
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function statusLabelWithOffice(): string
    {
        if ($this->status === 'completed') {
            $lastLog = $this->routingLogs
                ->where('action', 'completed')
                ->last();
            
            $lastOffice = $lastLog?->fromOffice?->name ?? $this->currentOffice?->name ?? 'Office';
            $statusText = 'Transaction Completed - ' . $lastOffice;
            
            return $statusText;
        }
        
        return $this->statusLabel();
    }

    public function getSubjectAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::upper((string) $value);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#64748b';
    }

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submittedToOffice()
    {
        return $this->belongsTo(Office::class, 'submitted_to_office_id');
    }

    public function currentOffice()
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function currentHandler()
    {
        return $this->belongsTo(User::class, 'current_handler_id');
    }

    public function routingLogs()
    {
        return $this->hasMany(RoutingLog::class)->orderBy('created_at', 'asc');
    }

    public function latestRoutingLog()
    {
        return $this->hasOne(RoutingLog::class)->latestOfMany();
    }

    /**
     * Internal documents are office-to-office submissions from office accounts or superadmins.
     */
    public function isInternalOfficeSubmission(): bool
    {
        if ($this->user) {
            return $this->user->isOfficeAccount() || $this->user->isSuperAdmin();
        }

        $legacySenderOffice = trim((string) ($this->sender_office ?? ''));
        $legacyRecipientOffice = trim((string) ($this->recipient_office ?? ''));

        return !$this->user_id
            && empty($this->sender_email)
            && $legacySenderOffice !== ''
            && $legacyRecipientOffice !== '';
    }

    /**
     * External documents come from outside the office-to-office workflow.
     */
    public function isExternal(): bool
    {
        return !$this->isInternalOfficeSubmission();
    }

    public function canCompleteTransactionFromCurrentStatus(): bool
    {
        return in_array($this->status, ['received', 'in_review', 'on_hold', 'for_pickup', 'returned'], true);
    }
}

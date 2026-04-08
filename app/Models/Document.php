<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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
            if ($doc->user_id) {
                Cache::forget('user_stats_' . $doc->user_id);
            }

            // Per-office cache (current office)
            if ($doc->current_office_id) {
                Cache::forget('office_stats_' . $doc->current_office_id);
            }

            // Per-office cache (submitted-to office)
            if ($doc->submitted_to_office_id) {
                Cache::forget('office_stats_' . $doc->submitted_to_office_id);
            }

            // ICT handler cache
            if ($doc->current_handler_id) {
                Cache::forget('ict_stats_' . $doc->current_handler_id);
            }
        };

        static::created($bustCache);
        static::updated($bustCache);
        static::deleted($bustCache);
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
        'returned'   => 'Returned',
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
        'returned'   => 'Returned',
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
}

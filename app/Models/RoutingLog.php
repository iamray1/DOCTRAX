<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'performed_by',
        'from_office_id',
        'to_office_id',
        'action',
        'status_after',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function fromOffice()
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice()
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }

    /**
     * Human-readable action label.
     */
    public function actionLabel(): string
    {
        $action = strtolower(trim((string) $this->action));
        $action = preg_replace('/\s+/', ' ', $action) ?: '';

        // Normalize legacy/free-text actions saved in older records.
        if (str_contains($action, 'kumuha') || str_contains($action, 'pick up') || str_contains($action, 'picked up')) {
            return 'Picked Up';
        }

        if (str_contains($action, 'receive') || str_contains($action, 'natanggap') || str_contains($action, 'stamp')) {
            return 'Received';
        }

        return match($action) {
            'submitted'  => 'Document Submitted',
            'received'   => 'Received',
            'processing' => 'Processing',
            'handoff'    => 'Internal Handoff',
            'in_review'  => 'Processing',
            'forwarded'  => 'Forwarded',
            'completed'  => 'Completed',
            'for_pickup' => 'Ready for Pickup',
            'returned'   => 'Returned',
            'cancelled'  => 'Cancelled',
            'archived'   => 'Archived (Unprocessed)',
            default      => ucfirst(str_replace('_', ' ', $action)),
        };
    }
}

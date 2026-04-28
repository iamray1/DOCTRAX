<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecordsController extends Controller
{
    private const SEARCH_MAX_LENGTH = 100;
    private const IDENTIFIER_SEARCH_MIN_LENGTH = 6;

    private const STATUS_FILTER_OPTIONS = [
        'submitted'  => 'Awaiting Receipt',
        'processing' => 'Processing',
        'completed'  => 'Completed',
        'archived'   => 'Archived',
    ];

    /**
     * Ensure user is either a Records Section representative or a SuperAdmin.
     */
    private function authorizeRecordsAccess()
    {
        $user = Auth::user();
        if (!$user) abort(403);

        // SuperAdmin always has access
        if ($user->isSuperAdmin()) return;

        // Records Section representative
        if ($user->isRecords()) return;

        abort(403, 'Unauthorized. Only Records Section or Super Admin can access this.');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function isIdentifierSearch(string $search): bool
    {
        $compact = preg_replace('/[^A-Z0-9]/i', '', $search) ?? '';

        return strlen($compact) >= self::IDENTIFIER_SEARCH_MIN_LENGTH
            && preg_match('/^[A-Z0-9\-\s]+$/i', $search);
    }

    private function shouldApplyKeywordSearch(string $search): bool
    {
        return strlen($search) >= 2 || $this->isIdentifierSearch($search);
    }

    private function applyKeywordSearch($query, string $search): void
    {
        $escaped = $this->escapeLike($search);
        $like = "%{$escaped}%";

        if ($this->isIdentifierSearch($search)) {
            $identifierPrefix = strtoupper(preg_replace('/\s+/', '', $search) ?? $search);
            $compactPrefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $search) ?? $search);
            $identifierLike = $this->escapeLike($identifierPrefix) . '%';
            $compactLike = $this->escapeLike($compactPrefix) . '%';

            $query->where(function ($q) use ($identifierLike, $compactLike, $like) {
                $q->where('reference_number', 'like', $compactLike)
                  ->orWhere('tracking_number', 'like', $identifierLike)
                  ->orWhere('reference_number', 'like', $like)
                  ->orWhere('tracking_number', 'like', $like);
            });

            return;
        }

        $query->where(function ($q) use ($like) {
            $q->where('reference_number', 'like', $like)
              ->orWhere('tracking_number', 'like', $like)
              ->orWhere('subject', 'like', $like)
              ->orWhere('sender_name', 'like', $like)
              ->orWhere('type', 'like', $like);
        });
    }

    private function recordsStats(): array
    {
        return Cache::remember('records_stats', 15, function () {
            $row = Document::query()
                ->selectRaw(
                    "COUNT(*) as total,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as received,
                    SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as archived",
                    ['submitted', 'received', 'in_review', 'completed', 'for_pickup', 'archived']
                )
                ->first();

            return [
                'total'     => (int) ($row->total ?? 0),
                'submitted' => (int) ($row->submitted ?? 0),
                'received'  => (int) ($row->received ?? 0),
                'completed' => (int) ($row->completed ?? 0),
                'archived'  => (int) ($row->archived ?? 0),
            ];
        });
    }

    /**
     * All incoming documents dashboard — shows every document in the system.
     */
    public function index(Request $request)
    {
        $this->authorizeRecordsAccess();
        $user = Auth::user();

        $query = Document::with(['user', 'submittedToOffice', 'currentOffice', 'currentHandler']);

        // Search
        $search = substr(strip_tags(trim((string) $request->get('search', ''))), 0, self::SEARCH_MAX_LENGTH);
        if ($search !== '' && $this->shouldApplyKeywordSearch($search)) {
            $this->applyKeywordSearch($query, $search);
        }

        // Status filter
        $status = trim((string) $request->get('status', ''));
        if ($status !== '' && array_key_exists($status, self::STATUS_FILTER_OPTIONS)) {
            $this->applyStatusFilter($query, $status);
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        $stats = $this->recordsStats();

        $statusOptions = self::STATUS_FILTER_OPTIONS;

        return view('records.index', compact('user', 'documents', 'stats', 'search', 'status', 'statusOptions'));
    }

    /**
     * View a single document's full detail and routing history.
     */
    public function show($id)
    {
        $this->authorizeRecordsAccess();
        $user = Auth::user();

        $document = Document::with([
            'submittedToOffice',
            'currentOffice',
            'currentHandler',
            'user',
            'routingLogs.fromOffice',
            'routingLogs.toOffice',
            'routingLogs.performer',
        ])->findOrFail($id);

        return view('records.document', compact('user', 'document'));
    }

    /**
     * JSON stats for live refresh.
     */
    public function statsJson()
    {
        $this->authorizeRecordsAccess();

        $stats = $this->recordsStats();

        // User-specific flag (not cached)
        $stats['has_reports_access'] = auth()->user()->hasReportsAccess();

        return response()->json($stats);
    }

    /**
     * Update document status (for ending transactions).
     */
    public function updateStatus(Request $request, $id)
    {
        $this->authorizeRecordsAccess();
        $request->validate([
            'status' => 'required|in:completed',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $document = Document::findOrFail($id);

        if ($document->status !== 'for_pickup') {
            return response()->json(['success' => false, 'message' => 'Only documents marked For Pickup can be ended.'], 422);
        }

        $document->status = 'completed';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => $document->current_office_id,
            'to_office_id' => $document->current_office_id,
            'action' => 'completed',
            'status_after' => 'completed',
            'remarks' => $request->remarks ?: 'Transaction ended by Records Section.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction ended. Document marked as Completed.',
            'status' => 'completed',
        ]);
    }

    private function applyStatusFilter($query, string $status): void
    {
        match ($status) {
            'submitted' => $query->where('status', 'submitted'),
            'processing' => $query->whereIn('status', ['received', 'in_review', 'on_hold']),
            'completed' => $query->whereIn('status', ['completed', 'for_pickup', 'returned']),
            'archived' => $query->where('status', 'archived'),
            default => null,
        };
    }
}

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

    /**
     * All incoming documents dashboard — shows every document in the system.
     */
    public function index(Request $request)
    {
        $this->authorizeRecordsAccess();
        $user = Auth::user();

        $query = Document::with(['user', 'submittedToOffice', 'currentOffice', 'currentHandler']);

        // Search
        $search = trim((string) $request->get('search', ''));
        $search = strip_tags($search);
        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('reference_number', 'like', "%{$escaped}%")
                  ->orWhere('tracking_number', 'like', "%{$escaped}%")
                  ->orWhere('subject', 'like', "%{$escaped}%")
                  ->orWhere('sender_name', 'like', "%{$escaped}%")
                  ->orWhere('type', 'like', "%{$escaped}%");
            });
        }

        // Status filter
        $status = trim((string) $request->get('status', ''));
        if ($status !== '' && array_key_exists($status, self::STATUS_FILTER_OPTIONS)) {
            $this->applyStatusFilter($query, $status);
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'       => Document::count(),
            'submitted'   => Document::where('status', 'submitted')->count(),
            'received'    => Document::whereIn('status', ['received', 'in_review'])->count(),
            'completed'   => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
            'archived'    => Document::where('status', 'archived')->count(),
        ];

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

        $stats = Cache::remember('records_stats', 15, function () {
            return [
                'total'     => Document::count(),
                'submitted' => Document::where('status', 'submitted')->count(),
                'received'  => Document::whereIn('status', ['received', 'in_review'])->count(),
                'completed' => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
                'archived'  => Document::where('status', 'archived')->count(),
            ];
        });

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

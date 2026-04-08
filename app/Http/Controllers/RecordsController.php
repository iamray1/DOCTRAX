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
        if ($status !== '' && array_key_exists($status, Document::FILTER_STATUSES)) {
            $query->where('status', $status);
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'       => Document::count(),
            'submitted'   => Document::where('status', 'submitted')->count(),
            'received'    => Document::whereIn('status', ['received', 'in_review'])->count(),
            'completed'   => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
            'archived'    => Document::where('status', 'archived')->count(),
        ];

        return view('records.index', compact('user', 'documents', 'stats', 'search', 'status'));
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
}

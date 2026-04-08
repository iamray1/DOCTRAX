<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private function adminDashboardResponse(array $data)
    {
        return response()
            ->view('admin.index', $data)
            ->header('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
    }

    public function index()
    {
        $user = Auth::user();

        // SuperAdmin: show admin dashboard with extra link to records view
        if ($user->isSuperAdmin()) {
            $recentDocs = Document::with('user', 'currentOffice')->latest()->take(10)->get();

            $data = [
                'user' => $user,
                'stats' => [
                    'total_users'     => User::where('role', 'user')->count(),
                    'total_documents' => Document::count(),
                    'pending_docs'    => Document::whereIn('status', ['submitted', 'received', 'in_review'])->count(),
                    'completed_docs'  => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
                ],
                'recentDocs' => $recentDocs,
            ];

            // If SuperAdmin has an office (e.g. ICT), include incoming office documents
            if ($user->office_id && $user->office) {
                $office = $user->office;
                $data['office'] = $office;

                $data['officeDocs'] = Document::with(['submittedToOffice', 'currentOffice', 'user'])
                    ->where(function ($q) use ($office) {
                        $q->where('current_office_id', $office->id)
                          ->orWhere(function ($sub) use ($office) {
                              $sub->where('status', 'submitted')
                                  ->where('submitted_to_office_id', $office->id);
                          });
                    })
                    ->whereIn('status', ['submitted', 'received', 'in_review', 'for_pickup'])
                    ->latest('last_action_at')
                    ->take(20)
                    ->get();

                $data['officeStats'] = [
                    'incoming'  => Document::where(function ($q) use ($office) {
                            $q->where('current_office_id', $office->id)
                              ->orWhere(function ($sub) use ($office) {
                                  $sub->where('status', 'submitted')
                                      ->where('submitted_to_office_id', $office->id);
                              });
                        })->whereIn('status', ['submitted', 'received', 'in_review'])->count(),
                    'in_review' => Document::where('current_office_id', $office->id)->whereIn('status', ['received', 'in_review'])->count(),
                    'completed' => Document::where('submitted_to_office_id', $office->id)
                        ->whereIn('status', ['completed', 'for_pickup'])->count(),
                ];
            }

            return $this->adminDashboardResponse($data);
        }

        if ($user->isAdmin()) {
            $recentDocs = Document::with('user', 'currentOffice')->latest()->take(10)->get();

            return $this->adminDashboardResponse([
                'user' => $user,
                'stats' => [
                    'total_users'     => User::where('role', 'user')->count(),
                    'total_documents' => Document::count(),
                    'pending_docs'    => Document::whereIn('status', ['submitted', 'received', 'in_review'])->count(),
                    'completed_docs'  => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
                ],
                'recentDocs' => $recentDocs,
            ]);
        }

        // Records Section representative: redirect to records documents view
        if ($user->isRecords()) {
            return redirect()->route('records.documents');
        }

        // Office account (representative with assigned office): redirect to office dashboard
        if ($user->isRepresentative() && $user->office_id) {
            return redirect()->route('office.dashboard');
        }

        // Regular individual user: their own submitted documents
        $myDocs = $user->documents()->with('currentOffice')->latest()->get();

        return view('dashboard.index', [
            'user'  => $user,
            'stats' => [
                'total'      => $myDocs->count(),
                'pending'    => $myDocs->whereIn('status', ['submitted', 'received', 'in_review', 'for_pickup'])->count(),
                'completed'  => $myDocs->whereIn('status', ['completed', 'returned'])->count(),
                'for_pickup' => $myDocs->where('status', 'for_pickup')->count(),
            ],
            'recentDocs'  => $myDocs->take(10),
            'pickupDocs'  => $myDocs->where('status', 'for_pickup')->values(),
        ]);
    }

    public function myDocuments(Request $request)
    {
        $user = Auth::user();

        $query = $user->documents()->with(['currentOffice', 'submittedToOffice'])->latest();

        $search = trim((string) $request->get('search', ''));
        $search = strip_tags($search);
        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('reference_number', 'like', '%' . $escaped . '%')
                  ->orWhere('tracking_number', 'like', '%' . $escaped . '%')
                  ->orWhere('subject', 'like', '%' . $escaped . '%')
                  ->orWhere('type', 'like', '%' . $escaped . '%');
            });
        }

        $status = trim((string) $request->get('status', ''));
        if ($status !== '' && array_key_exists($status, Document::FILTER_STATUSES)) {
            $query->where('status', $status);
        }

        $documents = $query->paginate(15)->withQueryString();

        if ($user->isAdmin()) {
            return view('admin.my-documents', compact('user', 'documents', 'search', 'status'));
        }

        if ($user->isRepresentative() && $user->office_id) {
            return view('office.my-documents', compact('user', 'documents', 'search', 'status'));
        }

        return view('dashboard.documents', compact('user', 'documents', 'search', 'status'));
    }

    // ─── Live stats JSON ───

    public function userStatsJson()
    {
        $user = Auth::user();
        $stats = Cache::remember('user_stats_' . $user->id, 15, function () use ($user) {
            $base = $user->documents();
            return [
                'total'     => (clone $base)->count(),
                'pending'   => (clone $base)->whereIn('status', ['submitted', 'received', 'in_review', 'for_pickup'])->count(),
                'completed' => (clone $base)->whereIn('status', ['completed', 'returned'])->count(),
            ];
        });
        return response()->json($stats);
    }

    public function adminStatsJson()
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) abort(403);
        $stats = Cache::remember('admin_stats', 15, function () {
            return [
                'total_users'    => User::where('role', 'user')->count(),
                'total_documents'=> Document::count(),
                'pending_docs'   => Document::whereIn('status', ['submitted', 'received', 'in_review'])->count(),
                'completed_docs' => Document::whereIn('status', ['completed', 'for_pickup'])->count(),
            ];
        });
        return response()->json($stats);
    }

    public function confirmPickup($reference)
    {
        $user = Auth::user();
        $lookup = strtoupper(trim((string) $reference));

        $document = Document::where('user_id', $user->id)
            ->where('status', 'for_pickup')
            ->where(function ($q) use ($lookup) {
                $q->whereRaw('UPPER(reference_number) = ?', [$lookup])
                  ->orWhereRaw('UPPER(tracking_number) = ?', [$lookup]);
            })
            ->first();

        if (!$document) {
            return response()->json(['success' => false, 'message' => 'Document not found or not eligible for pickup confirmation.'], 404);
        }

        $document->status         = 'completed';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id'    => $document->id,
            'performed_by'   => $user->id,
            'from_office_id' => $document->current_office_id,
            'to_office_id'   => null,
            'action'         => 'completed',
            'status_after'   => 'completed',
            'remarks'        => 'Recipient confirmed document receipt.',
        ]);

        return response()->json(['success' => true, 'message' => 'Receipt confirmed. Document marked as Completed.']);
    }

    /**
     * Cancel a document that the submitter owns and is still in 'submitted' status.
     */
    public function cancelDocument($reference)
    {
        $user = Auth::user();
        $lookup = strtoupper(trim((string) $reference));

        $document = Document::where('user_id', $user->id)
            ->where('status', 'submitted')
            ->where(function ($q) use ($lookup) {
                $q->whereRaw('UPPER(reference_number) = ?', [$lookup])
                  ->orWhereRaw('UPPER(tracking_number) = ?', [$lookup]);
            })
            ->first();

        if (!$document) {
            return response()->json(['success' => false, 'message' => 'Document not found or can no longer be cancelled.'], 404);
        }

        $document->status         = 'cancelled';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id'    => $document->id,
            'performed_by'   => $user->id,
            'from_office_id' => $document->submitted_to_office_id,
            'to_office_id'   => null,
            'action'         => 'cancelled',
            'status_after'   => 'cancelled',
            'remarks'        => 'Document cancelled by the submitter before office acceptance.',
        ]);

        return response()->json(['success' => true, 'message' => 'Document has been cancelled successfully.']);
    }
}

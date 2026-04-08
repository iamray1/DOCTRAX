<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RepresentativeController extends Controller
{
    private function rep()
    {
        return Auth::user();
    }

    /**
     * Ensure the current user is an active representative (or superadmin) with an office assigned.
     */
    private function authorizeRep()
    {
        $user = $this->rep();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        // SuperAdmins with an assigned office may also use office-level actions
        if ($user->isSuperAdmin() && $user->office_id) {
            return;
        }
        if (!$user->isRepresentative() || !$user->office_id) {
            abort(403, 'Unauthorized. You must be an assigned representative.');
        }
    }

    /**
     * Hide only the current user's untouched self-submissions from office queue views.
     * Guest/public submissions (user_id null) must remain visible, and once a document
     * is actually tagged to the current rep it should appear in the queue.
     */
    private function applyOfficeQueueVisibility($query, $user)
    {
        return $query->where(function ($q) use ($user) {
                        // Queue should only show items available to this user:
                        // unassigned documents OR documents currently tagged to this user.
                        $q->whereNull('current_handler_id')
                            ->orWhere('current_handler_id', $user->id);
                })->where(function ($q) use ($user) {
                        // Keep public submissions visible; hide untouched self-submissions.
                        $q->whereNull('user_id')
                            ->orWhere('user_id', '!=', $user->id)
                            ->orWhere('current_handler_id', $user->id);
        });
    }

    public function dashboard()
    {
        $this->authorizeRep();
        $user = $this->rep();

        // Admins/SuperAdmins have their own dashboard (which already includes office stats)
        if ($user->isAdmin()) {
            return redirect('/dashboard');
        }

        $office = $user->office;

        $isRecordsOffice = $user->isRecords();

        // Records receives docs via tracking number — don't show unprocessed 'submitted' docs in their queue
        $incoming = $this->applyOfficeQueueVisibility(
            Document::with(['submittedToOffice', 'currentOffice']),
            $user
        )
            ->where(function ($q) use ($office, $isRecordsOffice) {
                if ($isRecordsOffice) {
                    // Records: only docs already at their office (received via tracking #)
                    $q->where('current_office_id', $office->id);
                } else {
                    $q->where('current_office_id', $office->id)
                      ->orWhere(function ($sub) use ($office) {
                          $sub->where('status', 'submitted')
                              ->where('submitted_to_office_id', $office->id);
                      });
                }
            })
            ->when($isRecordsOffice,
                fn ($q) => $q->whereNotIn('status', ['submitted', 'completed', 'returned', 'cancelled', 'archived']),
                fn ($q) => $q->whereNotIn('status', ['completed', 'returned', 'cancelled', 'archived'])
            )
            ->latest('last_action_at')
            ->paginate(20);

        $stats = [
            'incoming' => $this->applyOfficeQueueVisibility(
                    Document::query(),
                    $user
                )->where(function ($q) use ($office, $isRecordsOffice) {
                    if ($isRecordsOffice) {
                        $q->where('current_office_id', $office->id);
                    } else {
                        $q->where('current_office_id', $office->id)
                          ->orWhere(function ($sub) use ($office) {
                              $sub->where('status', 'submitted')
                                  ->where('submitted_to_office_id', $office->id);
                          });
                    }
                })
                ->when($isRecordsOffice,
                    fn ($q) => $q->whereIn('status', ['received', 'in_review']),
                    fn ($q) => $q->whereIn('status', ['submitted', 'received', 'in_review'])
                )
                ->count(),
            'received' => $this->applyOfficeQueueVisibility(
                    Document::where('current_office_id', $office->id),
                    $user
                )->where('status', 'received')->count(),
            'in_review' => $this->applyOfficeQueueVisibility(
                    Document::where('current_office_id', $office->id),
                    $user
                )->where('status', 'in_review')->count(),
            // Count documents this specific office user finalized (completed/returned).
            'completed' => RoutingLog::query()
                ->where('performed_by', $user->id)
                ->whereIn('action', ['completed', 'returned'])
                ->distinct('document_id')
                ->count('document_id'),
            'for_pickup' => $this->applyOfficeQueueVisibility(
                    Document::where('current_office_id', $office->id),
                    $user
                )->where('status', 'for_pickup')->count(),
        ];

        $documents = $incoming;

        return response()
            ->view('office.dashboard', compact('user', 'office', 'documents', 'stats'))
            ->header('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
    }

    public function show($id)
    {
        $this->authorizeRep();
        $user = $this->rep();
        $office = $user->office;

        $document = Document::with([
            'submittedToOffice',
            'currentOffice',
            'currentHandler',
            'user',
            'routingLogs.fromOffice',
            'routingLogs.toOffice',
            'routingLogs.performer',
        ])->findOrFail($id);

        // Scope check: rep can only view documents that are at their office,
        // were submitted to their office, or have routing history through their office.
        if (!$user->isSuperAdmin()) {
            $atOffice = (int) $document->current_office_id === (int) $office->id
                || (int) $document->submitted_to_office_id === (int) $office->id;

            if (!$atOffice) {
                $inHistory = $document->routingLogs->contains(function ($log) use ($office) {
                    return (int) $log->from_office_id === (int) $office->id
                        || (int) $log->to_office_id === (int) $office->id;
                });
                if (!$inHistory) {
                    abort(403, 'This document is not associated with your office.');
                }
            }
        }

        return view('office.document', compact('user', 'office', 'document'));
    }

    public function accept(Request $request, $id)
    {
        $this->authorizeRep();
        $user = $this->rep();
        $office = $user->office;

        $document = Document::findOrFail($id);

        if ($document->status !== 'submitted') {
            return response()->json(['success' => false, 'message' => 'Document cannot be accepted at its current status.'], 422);
        }

        if ((int) $document->submitted_to_office_id !== (int) $office->id) {
            return response()->json(['success' => false, 'message' => 'This document is not addressed to your office.'], 403);
        }

        if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
            $handlerName = optional($document->currentHandler)->name ?: 'another office user';
            return response()->json([
                'success' => false,
                'message' => "This document is currently tagged to {$handlerName}.",
            ], 409);
        }

        $fromOfficeId = null;

        $document->current_office_id = $office->id;
        $document->current_handler_id = $user->id;
        $document->status = 'in_review';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => $fromOfficeId,
            'to_office_id' => $office->id,
            'action' => 'processing',
            'status_after' => 'in_review',
            'remarks' => $request->input('remarks', 'Document is now being processed.'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document accepted successfully.',
            'status' => 'in_review',
            'current_handler' => $user->name,
        ]);
    }

    public function receiveByReference(Request $request)
    {
        $this->authorizeRep();
        $request->validate([
            'reference_number' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $user = $this->rep();
        $office = $user->office;
        $lookupInput = strtoupper(trim(strip_tags((string)($request->reference_number ?: $request->tracking_number))));

        if ($lookupInput === '') {
            return response()->json([
                'success' => false,
                'message' => 'Reference number is required.',
            ], 422);
        }

        return DB::transaction(function () use ($lookupInput, $office, $user, $request) {
            $document = Document::with('currentHandler')->where(function ($q) use ($lookupInput) {
                $q->whereRaw('UPPER(reference_number) = ?', [$lookupInput])
                  ->orWhereRaw('UPPER(tracking_number) = ?', [$lookupInput]);
            })->lockForUpdate()->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Reference number not found.',
            ], 404);
        }

        if (in_array($document->status, ['completed', 'returned', 'cancelled', 'archived'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This document is already closed and cannot be received.',
            ], 422);
        }

        // Document is already physically at this office.
        if ($document->current_office_id === $office->id
            && in_array($document->status, ['received', 'in_review', 'for_pickup'], true)) {

            // Already tagged to this exact user
            if ($document->current_handler_id && (int) $document->current_handler_id === (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document is already at your office and tagged to you (' . $document->statusLabel() . ').',
                ], 422);
            }

            // At this office but tagged to another colleague — allow handoff by scan.
            if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
                $previousHandlerName = optional($document->currentHandler)->name ?: 'another office user';
                $document->current_handler_id = $user->id;
                $document->last_action_at = now();
                $document->save();

                RoutingLog::create([
                    'document_id' => $document->id,
                    'performed_by' => $user->id,
                    'from_office_id' => $office->id,
                    'to_office_id' => $office->id,
                    'action' => 'handoff',
                    'status_after' => $document->status,
                    'remarks' => $request->input('remarks', "Internal handoff within {$office->name}: {$previousHandlerName} to {$user->name} via scan."),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Document handoff complete. You are now the handler.",
                    'status' => $document->status,
                    'reference_number' => $document->reference_number ?: $document->tracking_number,
                    'tracking_number' => $document->tracking_number,
                    'current_handler' => $user->name,
                ]);
            }

            // At this office but unassigned — just tag the user without creating a new log entry
            $document->current_handler_id = $user->id;
            $document->save();
            return response()->json([
                'success' => true,
                'message' => 'This document is already at your office. You have been set as the handler.',
                'status' => $document->status,
                'reference_number' => $document->reference_number ?: $document->tracking_number,
                'tracking_number' => $document->tracking_number,
                'current_handler' => $user->name,
            ]);
        }

        $fromOfficeId = $document->current_office_id;
        $fromOfficeName = $fromOfficeId ? Office::whereKey($fromOfficeId)->value('name') : null;

        $document->current_office_id = $office->id;
        $document->current_handler_id = $user->id;
        $document->status = 'in_review';
        $document->last_action_at = now();

        if (!$document->submitted_to_office_id) {
            $document->submitted_to_office_id = $office->id;
        }

        $document->save();

        $defaultRemarks = $fromOfficeName
            ? "Document handed off from {$fromOfficeName} to {$office->name}."
            : "Document is now being processed at {$office->name}.";

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => $fromOfficeId,
            'to_office_id' => $office->id,
            'action' => 'processing',
            'status_after' => 'in_review',
            'remarks' => $request->input('remarks', $defaultRemarks),
        ]);

        $message = $fromOfficeName
            ? "Document accepted from {$fromOfficeName}."
            : 'Document is now being processed.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => 'in_review',
                'reference_number' => $document->reference_number ?: $document->tracking_number,
                'tracking_number' => $document->tracking_number,
                'current_office' => $office->name,
                'current_handler' => $user->name,
            ]);
        }, 3);
    }

    public function updateStatus(Request $request, $id)
    {
        $this->authorizeRep();
        $request->validate([
            'status' => 'required|in:completed,for_pickup,returned',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $user = $this->rep();
        $office = $user->office;

        $document = Document::findOrFail($id);

        if ($document->current_office_id !== $office->id) {
            return response()->json(['success' => false, 'message' => 'This document is not at your office.'], 403);
        }

        if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
            $handlerName = optional($document->currentHandler)->name ?: 'another office user';
            return response()->json([
                'success' => false,
                'message' => "This document is tagged to {$handlerName}.",
            ], 409);
        }

        if (!$document->current_handler_id) {
            $document->current_handler_id = $user->id;
        }

        $newStatus = $request->status;
        $document->status = $newStatus;
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => null,          // in-place update — no office transfer
            'to_office_id' => $office->id,
            'action' => $newStatus,
            'status_after' => $newStatus,
            'remarks' => $request->remarks ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document status updated to ' . Document::STATUSES[$newStatus] . '.',
            'status' => $newStatus,
        ]);
    }

    public function search(Request $request)
    {
        $user = $this->rep();

        // Only users with reports access (SuperAdmin, Records, or granted) may access reports
        if (!$user || !$user->hasReportsAccess()) {
            abort(403, 'Unauthorized. You do not have access to reports.');
        }

        $office = $user->office; // may be null for SuperAdmin

        $panel = 'documents'; // kept for backward compat, panel tabs removed
        $userId = (int) $request->query('user_id', 0);
        $selectedUser = null;

        $query = Document::query()
            ->with(['user', 'submittedToOffice', 'currentOffice', 'currentHandler']);

        // Scope to the user's office — only show documents that touched this office
        if ($office) {
            $officeId = $office->id;
            $query->where(function ($q) use ($officeId) {
                $q->where('current_office_id', $officeId)
                  ->orWhere('submitted_to_office_id', $officeId)
                  ->orWhereHas('routingLogs', function ($rl) use ($officeId) {
                      $rl->where('from_office_id', $officeId)
                        ->orWhere('to_office_id', $officeId);
                  });
            });
            // Exclude documents still in "submitted" status — only show received/handled docs
            $query->where('status', '!=', 'submitted');
        }

        // Filter by specific user (View Activity from Users panel)
        if ($userId > 0) {
            $selectedUser = \App\Models\User::find($userId);
            $query->where(function ($q) use ($userId) {
                $q->where('current_handler_id', $userId)
                  ->orWhere('user_id', $userId)
                  ->orWhereHas('routingLogs', function ($rl) use ($userId) {
                      $rl->where('performed_by', $userId);
                  });
            });
        }

        $search = trim((string) $request->query('search', ''));
        $search = strip_tags($search);
        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));
        $dateField = trim((string) $request->query('date_field', 'created_at'));
        if (!in_array($dateField, ['created_at', 'last_action_at'], true)) {
            $dateField = 'created_at';
        }
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $needle = '%' . strtolower($escaped) . '%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw("LOWER(COALESCE(reference_number, '')) LIKE ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(tracking_number, '')) LIKE ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(subject, '')) LIKE ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(sender_name, '')) LIKE ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(sender_office, '')) LIKE ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(type, '')) LIKE ?", [$needle])
                    // Search by current handler (tagged-to user) name
                    ->orWhereHas('currentHandler', function ($u) use ($needle) {
                        $u->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle]);
                    })
                    // Search by submitter (account holder) name
                    ->orWhereHas('user', function ($u) use ($needle) {
                        $u->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle]);
                    })
                    // Search by current office name
                    ->orWhereHas('currentOffice', function ($o) use ($needle) {
                        $o->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle]);
                    })
                    // Search by submitted-to office name
                    ->orWhereHas('submittedToOffice', function ($o) use ($needle) {
                        $o->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle]);
                    })
                    // Search by any user who performed an action (routing log)
                    ->orWhereHas('routingLogs.performer', function ($u) use ($needle) {
                        $u->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle]);
                    });
            });
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['received', 'in_review', 'on_hold'],
                'processed' => ['completed', 'for_pickup', 'returned'],
            ];
            if (isset($statusGroups[$status])) {
                $query->whereIn('status', $statusGroups[$status]);
            } elseif (array_key_exists($status, Document::FILTER_STATUSES)) {
                $query->where('status', $status);
            }
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }

        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $query->whereDate($dateField, '<=', $dateTo);
        }

        if ($request->query('export') === 'pdf') {
            $dateFieldLabel = $dateField === 'last_action_at' ? 'Last Action Date' : 'Submitted Date';
            $rows = (clone $query)
                ->orderByDesc($dateField)
                ->orderByDesc('id')
                ->get();

            $fileName = 'report-' . ($office ? strtolower(str_replace(' ', '-', $office->name)) . '-' : '') . now()->setTimezone('Asia/Manila')->format('Ymd-His') . '.pdf';

            $pdf = Pdf::loadView('pdf.report', [
                'rows'           => $rows,
                'officeName'     => $office?->name ?? 'Office',
                'generatedAt'    => now()->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
                'searchLabel'    => $search !== '' ? $search : 'All',
                'statusLabel'    => $status !== '' ? (Document::STATUSES[$status] ?? $status) : 'All',
                'typeLabel'      => $type !== '' ? $type : 'All',
                'dateFieldLabel' => $dateFieldLabel,
                'dateFromLabel'  => $dateFrom !== '' ? $dateFrom : 'N/A',
                'dateToLabel'    => $dateTo !== '' ? $dateTo : 'N/A',
            ])->setPaper('a4', 'portrait');

            return $pdf->download($fileName);
        }

        $documents = (clone $query)
            ->orderByDesc($dateField)
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'documents_page')
            ->withQueryString();

        $reportStats = [
            'total' => (clone $query)->count(),
            'processing' => (clone $query)->whereIn('status', ['received', 'in_review'])->count(),
            'completed' => (clone $query)->whereIn('status', ['completed', 'for_pickup', 'returned'])->count(),
        ];

        $typesQuery = Document::query()->whereNotNull('type');
        if ($office) {
            $officeId = $office->id;
            $typesQuery->where(function ($q) use ($officeId) {
                $q->where('current_office_id', $officeId)
                  ->orWhere('submitted_to_office_id', $officeId)
                  ->orWhereHas('routingLogs', function ($rl) use ($officeId) {
                      $rl->where('from_office_id', $officeId)
                        ->orWhere('to_office_id', $officeId);
                  });
            });
        }
        $availableTypes = $typesQuery
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $filters = [
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'date_field' => $dateField,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $statusOptions = Document::STATUSES;

        $statusGroups = [
            'pending'   => ['submitted', 'received', 'in_review', 'on_hold'],
            'processed' => ['completed', 'for_pickup', 'returned'],
        ];
        $reportStatusOptions = [
            'pending'   => 'Pending',
            'processed' => 'Processed',
        ];

        // ─── Office Staff Performance ────────────────────────────────────────
        $usersQuery = \App\Models\User::with('office')
            ->where('status', 'active')
            ->where('account_type', 'representative')
            ->whereNotIn('role', ['admin', 'superadmin']);

        // Scope to the user's office
        if ($office) {
            $usersQuery->where('office_id', $office->id);
        }

        $usersQuery->withCount('routingLogs as actions_count')
            ->withCount('handledDocuments as handling_count')
            ->withCount(['handledDocuments as handled_completed_count' => fn ($q) =>
                $q->whereIn('status', ['completed'])
            ])
            ->withCount(['handledDocuments as handled_received_count' => fn ($q) =>
                $q->whereIn('status', ['in_review'])
            ])
            ->withCount(['handledDocuments as handled_pending_count' => fn ($q) =>
                $q->whereIn('status', ['submitted', 'in_review', 'on_hold'])
            ])
            ->withCount(['handledDocuments as handled_processed_count' => fn ($q) =>
                $q->whereIn('status', ['completed', 'for_pickup', 'returned'])
            ]);

        $users = $usersQuery
            ->orderByDesc('handling_count')
            ->orderByDesc('actions_count')
            ->paginate(24, ['*'], 'users_page')->withQueryString();

        return view('office.search', compact(
            'user',
            'office',
            'documents',
            'reportStats',
            'availableTypes',
            'filters',
            'reportStatusOptions',
            'panel',
            'userId',
            'selectedUser',
            'users'
        ));
    }

    public function userActivityJson(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->hasReportsAccess()) {
            abort(403);
        }

        $u = \App\Models\User::with('office')->find($id);
        if (!$u) return response()->json(['success' => false, 'message' => 'User not found.'], 404);

        $isRep = $u->account_type === 'representative';
        $rawName = $u->name;

        if ($isRep && str_contains($rawName, ' - ')) {
            [$officePart, $displayName] = explode(' - ', $rawName, 2);
            $officeName = $u->office?->name ?? $officePart;
        } else {
            $displayName = $rawName;
            $officeName  = $u->office?->name ?? null;
        }

        $search   = trim((string) $request->query('search', ''));
        $status   = trim((string) $request->query('status', ''));
        $scope    = trim((string) $request->query('scope', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));

        $query = Document::with(['submittedToOffice', 'currentOffice', 'currentHandler']);

        // Only show documents handled/processed by this staff member (not their own submissions)
        $query->where(function ($q) use ($id) {
            $q->where('current_handler_id', $id)
              ->orWhereHas('routingLogs', fn ($rl) => $rl->where('performed_by', $id));
        })->where('user_id', '!=', $id);

        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $needle = '%' . strtolower($escaped) . '%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw("LOWER(COALESCE(reference_number,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(tracking_number,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(subject,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(sender_name,'')) LIKE ?", [$needle]);
            });
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['submitted', 'received', 'in_review', 'on_hold'],
                'processed' => ['completed', 'for_pickup', 'returned'],
            ];
            if (isset($statusGroups[$status])) {
                $query->whereIn('status', $statusGroups[$status]);
            }
        }

        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $docs = $query->orderByDesc('last_action_at')->orderByDesc('id')->get();

        $actionsCount = $isRep
            ? \App\Models\RoutingLog::where('performed_by', $id)->count()
            : 0;

        $docsData = $docs->map(function ($doc) {
            $currentOffice = $doc->status === 'submitted'
                ? 'Awaiting: ' . ($doc->submittedToOffice?->name ?? 'Records')
                : ($doc->currentOffice?->name ?? $doc->submittedToOffice?->name ?? '-');
            return [
                'id'           => $doc->id,
                'reference'    => $doc->reference_number ?: $doc->tracking_number,
                'tracking'     => $doc->tracking_number,
                'subject'      => $doc->subject,
                'type'         => $doc->type,
                'status'       => $doc->status,
                'status_label' => $doc->statusLabel(),
                'status_color' => $doc->statusColor(),
                'current_office' => $currentOffice,
                'submitted_at' => $doc->created_at?->copy()->setTimezone('Asia/Manila')->format('M d, Y'),
                'last_action'  => $doc->last_action_at?->copy()->setTimezone('Asia/Manila')->format('M d, Y') ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'user' => [
                'id'           => $u->id,
                'name'         => $displayName,
                'office'       => $officeName,
                'email'        => $u->email,
                'account_type' => $u->account_type,
                'is_rep'       => $isRep,
            ],
            'stats' => [
                'total_docs'  => $docs->count(),
                'pending'     => $docs->whereIn('status', ['submitted', 'received', 'in_review', 'on_hold'])->count(),
                'processed'   => $docs->whereIn('status', ['completed', 'for_pickup', 'returned'])->count(),
                'actions'     => $actionsCount,
            ],
            'documents' => $docsData,
        ]);
    }

    public function userActivityExport(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->hasReportsAccess()) {
            abort(403);
        }

        $u = \App\Models\User::with('office')->findOrFail($id);
        $isRep   = $u->account_type === 'representative';
        $rawName = $u->name;

        if ($isRep && str_contains($rawName, ' - ')) {
            [$officePart, $displayName] = explode(' - ', $rawName, 2);
            $officeName = $u->office?->name ?? $officePart;
        } else {
            $displayName = $rawName;
            $officeName  = $u->office?->name ?? $u->email;
        }

        $search   = trim((string) $request->query('search', ''));
        $status   = trim((string) $request->query('status', ''));
        $scope    = trim((string) $request->query('scope', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));
        $format   = $request->query('format', 'pdf');

        $query = Document::with(['submittedToOffice', 'currentOffice', 'currentHandler']);

        // Only show documents handled/processed by this staff member
        $query->where(function ($q) use ($id) {
            $q->where('current_handler_id', $id)
              ->orWhereHas('routingLogs', fn ($rl) => $rl->where('performed_by', $id));
        })->where('user_id', '!=', $id);

        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $needle = '%' . strtolower($escaped) . '%';
            $query->where(function ($q) use ($needle) {
                $q->whereRaw("LOWER(COALESCE(reference_number,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(tracking_number,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(subject,'')) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(COALESCE(sender_name,'')) LIKE ?", [$needle]);
            });
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['submitted', 'received', 'in_review', 'on_hold'],
                'processed' => ['completed', 'for_pickup', 'returned'],
            ];
            if (isset($statusGroups[$status])) {
                $query->whereIn('status', $statusGroups[$status]);
            }
        }

        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $docs = $query->orderByDesc('last_action_at')->orderByDesc('id')->get();
        $generatedAt = now()->setTimezone('Asia/Manila')->format('M d, Y h:i A');

        if ($format === 'pdf') {
            $fileName = 'user-report-' . \Illuminate\Support\Str::slug($displayName) . '-' . now()->format('Ymd-His') . '.pdf';

            $pdf = Pdf::loadView('pdf.user-report', [
                'docs'          => $docs,
                'displayName'   => $displayName,
                'officeName'    => $officeName,
                'generatedAt'   => $generatedAt,
                'searchLabel'   => $search ?: 'All',
                'statusLabel'   => $status ? (Document::STATUSES[$status] ?? $status) : 'All',
                'dateFromLabel' => $dateFrom ?: 'N/A',
                'dateToLabel'   => $dateTo ?: 'N/A',
            ])->setPaper('a4', 'portrait');

            return $pdf->download($fileName);
        }

        // Print HTML
        $statusLabel = $status ? (Document::STATUSES[$status] ?? $status) : 'All';
        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<title>User Report - ' . htmlspecialchars($displayName) . '</title>';
        $html .= '<style>body{font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1b263b;padding:28px;line-height:1.5}';
        $html .= 'h2{font-size:20px;font-weight:700;margin:0 0 3px;color:#0056b3}';
        $html .= 'p{margin:2px 0;font-size:12px;color:#64748b}';
        $html .= '.meta{margin-bottom:18px;padding-bottom:14px;border-bottom:3px solid #0056b3}';
        $html .= '.filters{font-size:12px;color:#475569;margin-bottom:14px}';
        $html .= 'table{width:100%;border-collapse:collapse;margin-top:10px}';
        $html .= 'th{background:#0056b3;color:#fff;padding:9px 10px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}';
        $html .= 'td{padding:8px 10px;border-bottom:1px solid #e2e8f0;font-size:12px;line-height:1.45}';
        $html .= 'tr:nth-child(even) td{background:#f8fafc}';
        $html .= '.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase}';
        $html .= '@media print{body{padding:0}}</style></head><body>';
        $html .= '<div class="meta">';
        $html .= '<h2>User Activity Report - ' . htmlspecialchars($displayName) . '</h2>';
        $html .= '<p>Office/Type: ' . htmlspecialchars($officeName) . '</p>';
        $html .= '<p>Generated: ' . $generatedAt . '</p></div>';
        $html .= '<div class="filters">Filters — ';
        $html .= 'Keyword: <strong>' . ($search ?: 'All') . '</strong> &nbsp;|&nbsp; ';
        $html .= 'Status: <strong>' . $statusLabel . '</strong> &nbsp;|&nbsp; ';
        $html .= 'Date: <strong>' . ($dateFrom ?: 'N/A') . '</strong> to <strong>' . ($dateTo ?: 'N/A') . '</strong>';
        $html .= ' &nbsp;|&nbsp; Total: <strong>' . $docs->count() . '</strong></div>';
        $html .= '<table><thead><tr><th>#</th><th>Reference</th><th>Subject</th><th>Type</th><th>Sender</th>';
        $html .= '<th>Status</th><th>Office</th><th>Handler</th><th>Submitted At</th><th>Last Action</th></tr></thead><tbody>';
        foreach ($docs as $i => $doc) {
            $currentOffice = $doc->status === 'submitted'
                ? 'Awaiting: ' . ($doc->submittedToOffice?->name ?? 'Records')
                : ($doc->currentOffice?->name ?? $doc->submittedToOffice?->name ?? '-');
            $html .= '<tr><td>' . ($i + 1) . '</td>';
            $html .= '<td><code>' . htmlspecialchars($doc->reference_number ?: $doc->tracking_number) . '</code></td>';
            $html .= '<td>' . htmlspecialchars($doc->subject) . '</td>';
            $html .= '<td>' . htmlspecialchars($doc->type) . '</td>';
            $html .= '<td>' . htmlspecialchars($doc->sender_name) . '</td>';
            $html .= '<td><span class="badge">' . $doc->statusLabel() . '</span></td>';
            $html .= '<td>' . htmlspecialchars($currentOffice) . '</td>';
            $html .= '<td>' . htmlspecialchars($doc->currentHandler?->name ?? 'Unassigned') . '</td>';
            $html .= '<td>' . ($doc->created_at?->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') ?? '-') . '</td>';
            $html .= '<td>' . ($doc->last_action_at?->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') ?? '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<script>window.onload=function(){window.print();}<\/script>';
        $html .= '</body></html>';
        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function officeStatsJson()
    {
        $this->authorizeRep();
        $user = $this->rep();
        $office = $user->office;

        $isRecordsOffice = $user->isRecords();

        // Fresh stats every request — no cache, queries are lightweight and stats must be real-time
        $stats = [
            'incoming' => $this->applyOfficeQueueVisibility(
                    Document::query(),
                    $user
                )->where(function ($q) use ($office, $isRecordsOffice) {
                    if ($isRecordsOffice) {
                        $q->where('current_office_id', $office->id);
                    } else {
                        $q->where('current_office_id', $office->id)
                          ->orWhere(function ($sub) use ($office) {
                              $sub->where('status', 'submitted')
                                  ->where('submitted_to_office_id', $office->id);
                          });
                    }
                })
                ->when($isRecordsOffice,
                    fn ($q) => $q->whereIn('status', ['received', 'in_review']),
                    fn ($q) => $q->whereIn('status', ['submitted', 'received', 'in_review'])
                )
                ->count(),
            'in_review' => $this->applyOfficeQueueVisibility(
                    Document::where('current_office_id', $office->id),
                    $user
                )->whereIn('status', ['received', 'in_review'])->count(),
            'completed' => $this->applyOfficeQueueVisibility(
                    Document::where('submitted_to_office_id', $office->id),
                    $user
                )
                ->whereIn('status', ['completed', 'for_pickup'])
                ->count(),
            'for_pickup' => $this->applyOfficeQueueVisibility(
                    Document::where('current_office_id', $office->id),
                    $user
                )->where('status', 'for_pickup')->count(),
        ];

        // User-specific flag (not cached by office)
        $stats['has_reports_access'] = auth()->user()->hasReportsAccess();

        return response()->json($stats);
    }
}

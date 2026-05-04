<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use App\Services\ActivationService;
use App\Services\DocumentStatusEmailService;
use App\Support\SubmissionNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RepresentativeController extends Controller
{
    private const REPORT_SEARCH_MAX_LENGTH = 100;
    private const REPORT_IDENTIFIER_SEARCH_MIN_LENGTH = 6;
    private const REPORT_EXPORT_LIMIT = 500;
    private const LIVE_STATS_CACHE_SECONDS = 45;

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
     * Filter documents visible to the user in their queue.
     * Show documents that are: tagged to user, or user has routing history with.
     */
    private function applyOfficeQueueVisibility($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // Show documents where user is tagged OR user's office has routing history
            $q->where('current_handler_id', $user->id)
              ->orWhereHas('routingLogs', function ($rl) use ($user) {
                  $rl->where('from_office_id', $user->office_id);
              });
        });
    }

    private function excludeLatestOutboundHandoff($query, Office $office)
    {
        return $query->whereDoesntHave('latestRoutingLog', function ($log) use ($office) {
            $log->where('from_office_id', $office->id)
                ->whereNotNull('to_office_id')
                ->where('to_office_id', '!=', $office->id)
                ->whereIn('action', ['forwarded', 'processing']);
        });
    }

    public function dashboard(Request $request)
    {
        $this->authorizeRep();
        $user = $this->rep();

        // Admins/SuperAdmins have their own dashboard (which already includes office stats)
        if ($user->isAdmin()) {
            return redirect('/dashboard');
        }

        // School representatives should use regular user dashboard (submit only, no receive)
        if ($user->office && $user->office->is_school) {
            return redirect('/dashboard');
        }

        $office = $user->office;

        $queueStatuses = ['received', 'in_review', 'on_hold', 'for_pickup', 'completed', 'returned'];

        // Queue shows documents this account personally received/handles, including processed rows
        // so the dashboard status filter can show documents completed/returned by this user.
        // Own submissions stay in My Documents, even after they are received by the office.
        $queueSearch = trim(strip_tags((string) $request->get('queue_search', '')));
        $queueStatus = trim((string) $request->get('queue_status', ''));
        $statusForQuery = $queueStatus === 'processed' ? 'completed' : $queueStatus;

        $incoming = Document::with(['user.office', 'submittedToOffice', 'currentOffice', 'routingLogs'])
            ->where('current_office_id', $office->id)
            ->where('current_handler_id', $user->id)
            ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', '!=', $user->id);
            })
            ->whereIn('status', $queueStatuses)
            ->when($queueSearch !== '', function ($query) use ($queueSearch) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $queueSearch);
                $query->where(function ($q) use ($escaped) {
                    $q->where('reference_number', 'like', "%{$escaped}%")
                      ->orWhere('tracking_number', 'like', "%{$escaped}%")
                      ->orWhere('subject', 'like', "%{$escaped}%")
                      ->orWhere('sender_name', 'like', "%{$escaped}%")
                      ->orWhere('type', 'like', "%{$escaped}%");
                });
            })
            ->when($statusForQuery !== '' && in_array($statusForQuery, $queueStatuses, true), fn ($query) => $query->where('status', $statusForQuery))
            ->orderByRaw("CASE 
                WHEN status = 'received' THEN 1
                WHEN status = 'in_review' THEN 2
                WHEN status = 'for_pickup' THEN 3
                WHEN status = 'returned' THEN 4
                WHEN status = 'on_hold' THEN 5
                WHEN status = 'completed' THEN 6
                ELSE 7
            END")
            ->latest('last_action_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'incoming' => Document::query()
                ->where('current_office_id', $office->id)
                ->where('current_handler_id', $user->id)
                ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->whereIn('status', ['received', 'in_review'])
                ->count(),
            'received' => Document::where('current_office_id', $office->id)
                ->where('current_handler_id', $user->id)
                ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->where('status', 'received')->count(),
            'in_review' => Document::where('current_office_id', $office->id)
                ->where('current_handler_id', $user->id)
                ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->where('status', 'in_review')->count(),
            // Count documents this specific office user finalized (completed only).
            'processed' => RoutingLog::query()
                ->where('performed_by', $user->id)
                ->where('action', 'completed')
                ->distinct('document_id')
                ->count('document_id'),
            'for_pickup' => Document::where('current_office_id', $office->id)
                ->where('current_handler_id', $user->id)
                ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->where('status', 'for_pickup')->count(),
        ];

        $documents = $incoming;
        app(ActivationService::class)->linkGuestDocumentsForUser($user);
        $submissionNotificationData = SubmissionNotifications::forUser($user);

        return response()
            ->view('office.dashboard', array_merge(
                compact('user', 'office', 'documents', 'stats'),
                $submissionNotificationData
            ))
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
            'user.office',
            'routingLogs.fromOffice',
            'routingLogs.toOffice',
            'routingLogs.performer',
        ])->findOrFail($id);

        // Allow access if: SuperAdmin, currently tagged to user, at current office, or user has routing history
        if (!$user->isSuperAdmin()) {
            $canAccess = (int) $document->current_handler_id === (int) $user->id
                || (int) $document->current_office_id === (int) $office->id
                || (int) $document->submitted_to_office_id === (int) $office->id
                || $document->routingLogs->contains(function ($log) use ($user) {
                    return (int) $log->from_office_id === (int) $user->office_id;
                });

            if (!$canAccess) {
                abort(403, 'You do not have access to this document.');
            }
        }

        return view('office.document', compact('user', 'office', 'document'));
    }

    public function accept(Request $request, $id)
    {
        $this->authorizeRep();

        return response()->json([
            'success' => false,
            'message' => 'Direct accept is disabled. Receive the document using its reference number, tracking number, or QR scan.',
        ], 410);
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
            $document = Document::with(['currentHandler', 'user.office'])->where(function ($q) use ($lookupInput) {
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

        // Check if document needs to go through Records first (only for public submissions).
        $submittedInternally = $document->isInternalOfficeSubmission();
        $isInitialOfficeIntake = is_null($document->current_office_id);
        $isRecordsOffice = $user->isRecords();

        // Allow any office to receive documents that are already in circulation (current_office_id is set)
        // OR if submitted internally by an office/superadmin OR if this is Records office.
        if ($isInitialOfficeIntake && !$submittedInternally && !$isRecordsOffice) {
            // For public submissions on initial intake, only Records can receive
            // But if document is already forwarded/in circulation, any office can receive
            return response()->json([
                'success' => false,
                'message' => 'This document must be received by Records Section first. Your office can receive it by scan after Records has accepted it.',
            ], 403);
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

        $document = Document::with(['user.office', 'currentHandler'])->findOrFail($id);

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

        if ($document->isExternal() && !$user->isRecords()) {
            return response()->json([
                'success' => false,
                'message' => 'Outside-submitted documents can only have status updates from Records Section.',
            ], 403);
        }

        if (!$document->current_handler_id) {
            $document->current_handler_id = $user->id;
        }

        $newStatus = $request->status;
        if (in_array($document->status, ['completed', 'cancelled', 'archived'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This document is already closed.',
            ], 422);
        }

        if ($document->status === $newStatus) {
            $statusLabel = Document::STATUSES[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));

            return response()->json([
                'success' => false,
                'message' => "This document is already {$statusLabel}.",
            ], 422);
        }

        if ($newStatus === 'completed' && !$document->canCompleteTransactionFromCurrentStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Only For Pickup, For Return, or active office-to-office documents can be ended.',
            ], 422);
        }

        if ($document->status === 'returned' && $newStatus !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'For Return documents can only be ended.',
            ], 422);
        }

        $document->status = $newStatus;
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => null,
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

    public function bulkUpdateStatus(Request $request)
    {
        $this->authorizeRep();

        $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'integer',
            'status' => 'required|in:completed,for_pickup,returned',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $user = $this->rep();
        $office = $user->office;
        if (!$office) {
            return response()->json(['success' => false, 'message' => 'No office is assigned to your account.'], 403);
        }

        $ids = collect($request->input('document_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Select at least one document.'], 422);
        }

        $newStatus = (string) $request->input('status');
        $remarks = $request->input('remarks') ?: null;
        $documents = Document::with(['user.office', 'currentHandler'])
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy('id');

        $updated = 0;
        $failures = [];

        DocumentStatusEmailService::beginBulkEmailCapture();
        try {
            foreach ($ids as $id) {
                $document = $documents->get($id);

                if (!$document) {
                    $failures[] = ['id' => $id, 'message' => 'Document not found.'];
                    continue;
                }

                $label = $document->reference_number ?: ($document->tracking_number ?: ('Document #' . $document->id));

                if ((int) $document->current_office_id !== (int) $office->id) {
                    $failures[] = ['id' => $id, 'message' => "{$label} is not at your office."];
                    continue;
                }

                if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
                    $handlerName = optional($document->currentHandler)->name ?: 'another office user';
                    $failures[] = ['id' => $id, 'message' => "{$label} is tagged to {$handlerName}."];
                    continue;
                }

                if ($document->isExternal() && !$user->isRecords()) {
                    $failures[] = ['id' => $id, 'message' => "{$label} can only have status updates from Records Section."];
                    continue;
                }

                if (in_array($document->status, ['completed', 'cancelled', 'archived'], true)) {
                    $failures[] = ['id' => $id, 'message' => "{$label} is already closed."];
                    continue;
                }

                if ($document->status === $newStatus) {
                    $statusLabel = Document::STATUSES[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));
                    $failures[] = ['id' => $id, 'message' => "{$label} is already {$statusLabel}."];
                    continue;
                }

                if ($newStatus === 'completed' && !$document->canCompleteTransactionFromCurrentStatus()) {
                    $failures[] = ['id' => $id, 'message' => "{$label} must be For Pickup, For Return, or an active office-to-office transaction before ending."];
                    continue;
                }

                if ($document->status === 'returned' && $newStatus !== 'completed') {
                    $failures[] = ['id' => $id, 'message' => "{$label} is For Return and can only be ended."];
                    continue;
                }

                if (!$document->current_handler_id) {
                    $document->current_handler_id = $user->id;
                }

                $document->status = $newStatus;
                $document->last_action_at = now();
                $document->save();

                RoutingLog::create([
                    'document_id' => $document->id,
                    'performed_by' => $user->id,
                    'from_office_id' => null,
                    'to_office_id' => $office->id,
                    'action' => $newStatus,
                    'status_after' => $newStatus,
                    'remarks' => $remarks,
                ]);

                $updated++;
            }
        } finally {
            DocumentStatusEmailService::endBulkEmailCaptureAndSend();
        }

        $statusLabel = Document::STATUSES[$newStatus] ?? ucfirst($newStatus);
        $message = $updated . ' document(s) updated to ' . $statusLabel . '.';
        if ($failures) {
            $message .= ' ' . count($failures) . ' skipped.';
        }

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? $message : 'No documents were updated.',
            'updated_count' => $updated,
            'failed_count' => count($failures),
            'failures' => $failures,
        ], $updated > 0 ? 200 : 422);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function isIdentifierSearch(string $search): bool
    {
        $compact = preg_replace('/[^A-Z0-9]/i', '', $search) ?? '';

        return strlen($compact) >= self::REPORT_IDENTIFIER_SEARCH_MIN_LENGTH
            && preg_match('/^[A-Z0-9\-\s]+$/i', $search);
    }

    private function shouldApplyKeywordSearch(string $search): bool
    {
        return strlen($search) >= 2 || $this->isIdentifierSearch($search);
    }

    private function applyReportKeywordSearch($query, string $search): void
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
                ->orWhere('sender_office', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhereHas('currentHandler', function ($u) use ($like) {
                    $u->where('name', 'like', $like);
                })
                ->orWhereHas('user', function ($u) use ($like) {
                    $u->where('name', 'like', $like);
                })
                ->orWhereHas('currentOffice', function ($o) use ($like) {
                    $o->where('name', 'like', $like);
                })
                ->orWhereHas('submittedToOffice', function ($o) use ($like) {
                    $o->where('name', 'like', $like);
                })
                ->orWhereHas('routingLogs.performer', function ($u) use ($like) {
                    $u->where('name', 'like', $like);
                });
        });
    }

    private function dateBoundary(string $date, bool $endOfDay): ?\Carbon\Carbon
    {
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            $value = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Throwable) {
            return null;
        }

        if (!$value || $value->format('Y-m-d') !== $date) {
            return null;
        }

        return $endOfDay ? $value->endOfDay() : $value->startOfDay();
    }

    private function applyDateRangeFilter($query, string $dateField, string $dateFrom, string $dateTo): void
    {
        if ($from = $this->dateBoundary($dateFrom, false)) {
            $query->where($dateField, '>=', $from);
        }

        if ($to = $this->dateBoundary($dateTo, true)) {
            $query->where($dateField, '<=', $to);
        }
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

        // Scope to the user's office — only show documents that touched this office OR were handled by this user
        if ($office) {
            $officeId = $office->id;
            $query->where(function ($q) use ($officeId, $user) {
                $q->where('current_office_id', $officeId)
                  ->orWhere('submitted_to_office_id', $officeId)
                  ->orWhereHas('routingLogs', function ($rl) use ($officeId) {
                      $rl->where('from_office_id', $officeId)
                        ->orWhere('to_office_id', $officeId);
                  })
                  ->orWhere('current_handler_id', $user->id);
            });
        }

        // Filter by specific user (View Activity from Users panel)
        if ($userId > 0) {
            $selectedUser = \App\Models\User::find($userId);
            $query->where(function ($q) use ($userId, $selectedUser) {
                $q->where('current_handler_id', $userId)
                  ->orWhere('user_id', $userId);
                if ($selectedUser && $selectedUser->office_id) {
                    $q->orWhereHas('routingLogs', function ($rl) use ($selectedUser) {
                        $rl->where('from_office_id', $selectedUser->office_id);
                    });
                }
            });
        }

        $search = substr(strip_tags(trim((string) $request->query('search', ''))), 0, self::REPORT_SEARCH_MAX_LENGTH);
        $status = trim((string) $request->query('status', ''));
        $type = substr(strip_tags(trim((string) $request->query('type', ''))), 0, 120);
        $dateField = trim((string) $request->query('date_field', 'created_at'));
        if (!in_array($dateField, ['created_at', 'last_action_at'], true)) {
            $dateField = 'created_at';
        }
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        if ($search !== '' && $this->shouldApplyKeywordSearch($search)) {
            $this->applyReportKeywordSearch($query, $search);
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['received', 'in_review', 'on_hold'],
                'processed' => ['completed'],
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

        $this->applyDateRangeFilter($query, $dateField, $dateFrom, $dateTo);

        if ($request->query('export') === 'pdf') {
            $dateFieldLabel = $dateField === 'last_action_at' ? 'Last Action Date' : 'Submitted Date';
            $rows = (clone $query)
                ->orderByDesc($dateField)
                ->orderByDesc('id')
                ->limit(self::REPORT_EXPORT_LIMIT + 1)
                ->get();

            if ($rows->count() > self::REPORT_EXPORT_LIMIT) {
                return redirect()
                    ->route('office.search', $request->except('export'))
                    ->with('error', 'PDF export is limited to ' . self::REPORT_EXPORT_LIMIT . ' rows. Please narrow the report with search, status, type, or date filters first.');
            }

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

        $statsRow = (clone $query)
            ->withoutEagerLoads()
            ->selectRaw(
                "COUNT(*) as total,
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processed",
                ['received', 'in_review', 'completed']
            )
            ->first();

        $reportStats = [
            'total' => (int) ($statsRow->total ?? 0),
            'processing' => (int) ($statsRow->processing ?? 0),
            'processed' => (int) ($statsRow->processed ?? 0),
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
            'processed' => ['completed'],
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
                $q->whereIn('status', ['completed'])
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

        $search   = substr(strip_tags(trim((string) $request->query('search', ''))), 0, self::REPORT_SEARCH_MAX_LENGTH);
        $status   = trim((string) $request->query('status', ''));
        $scope    = trim((string) $request->query('scope', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));

        $query = Document::with(['submittedToOffice', 'currentOffice', 'currentHandler']);

        // Only show documents handled/processed by this staff member (not their own submissions)
        $query->where(function ($q) use ($id, $u) {
            $q->where('current_handler_id', $id);
            if ($u->office_id) {
                $q->orWhereHas('routingLogs', fn ($rl) => $rl->where('from_office_id', $u->office_id));
            }
        })->where('user_id', '!=', $id);

        if ($search !== '' && $this->shouldApplyKeywordSearch($search)) {
            $this->applyReportKeywordSearch($query, $search);
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['submitted', 'received', 'in_review', 'on_hold'],
                'processed' => ['completed'],
            ];
            if (isset($statusGroups[$status])) {
                $query->whereIn('status', $statusGroups[$status]);
            }
        }

        $this->applyDateRangeFilter($query, 'created_at', $dateFrom, $dateTo);

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
                'processed'   => $docs->whereIn('status', ['completed'])->count(),
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

        $search   = substr(strip_tags(trim((string) $request->query('search', ''))), 0, self::REPORT_SEARCH_MAX_LENGTH);
        $status   = trim((string) $request->query('status', ''));
        $scope    = trim((string) $request->query('scope', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));
        $format   = $request->query('format', 'pdf');

        $query = Document::with(['submittedToOffice', 'currentOffice', 'currentHandler']);

        // Only show documents handled/processed by this staff member
        $query->where(function ($q) use ($id, $u) {
            $q->where('current_handler_id', $id);
            if ($u->office_id) {
                $q->orWhereHas('routingLogs', fn ($rl) => $rl->where('from_office_id', $u->office_id));
            }
        })->where('user_id', '!=', $id);

        if ($search !== '' && $this->shouldApplyKeywordSearch($search)) {
            $this->applyReportKeywordSearch($query, $search);
        }

        if ($status !== '') {
            $statusGroups = [
                'pending'   => ['submitted', 'received', 'in_review', 'on_hold'],
                'processed' => ['completed'],
            ];
            if (isset($statusGroups[$status])) {
                $query->whereIn('status', $statusGroups[$status]);
            }
        }

        $this->applyDateRangeFilter($query, 'created_at', $dateFrom, $dateTo);

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

        $stats = Cache::remember(
            'office_stats_user_' . $office->id . '_' . $user->id,
            self::LIVE_STATS_CACHE_SECONDS,
            function () use ($office, $user) {
                return [
                    'incoming' => Document::query()
                        ->where('current_office_id', $office->id)
                        ->where('current_handler_id', $user->id)
                        ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                        ->where(function ($q) use ($user) {
                            $q->whereNull('user_id')
                              ->orWhere('user_id', '!=', $user->id);
                        })
                        ->whereIn('status', ['received', 'in_review'])
                        ->count(),
                    'in_review' => Document::where('current_office_id', $office->id)
                        ->where('current_handler_id', $user->id)
                        ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                        ->where(function ($q) use ($user) {
                            $q->whereNull('user_id')
                              ->orWhere('user_id', '!=', $user->id);
                        })
                        ->whereIn('status', ['received', 'in_review'])->count(),
                    'processed' => RoutingLog::query()
                        ->where('performed_by', $user->id)
                        ->where('action', 'completed')
                        ->distinct('document_id')
                        ->count('document_id'),
                    'completed' => Document::where('current_office_id', $office->id)
                        ->where('current_handler_id', $user->id)
                        ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                        ->where(function ($q) use ($user) {
                            $q->whereNull('user_id')
                              ->orWhere('user_id', '!=', $user->id);
                        })
                        ->whereIn('status', ['completed'])
                        ->count(),
                    'for_pickup' => Document::where('current_office_id', $office->id)
                        ->where('current_handler_id', $user->id)
                        ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
                        ->where(function ($q) use ($user) {
                            $q->whereNull('user_id')
                              ->orWhere('user_id', '!=', $user->id);
                        })
                        ->where('status', 'for_pickup')->count(),
                ];
            }
        );

        // User-specific flag (not cached by office)
        $stats['has_reports_access'] = auth()->user()->hasReportsAccess();

        return response()->json($stats);
    }
}

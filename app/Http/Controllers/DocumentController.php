<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use App\Models\User;
use App\Services\ReferenceNumberService;
use App\Services\TrackingNumberService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DocumentController extends Controller
{
    public function __construct(
        private TrackingNumberService $trackingNumberService,
        private ReferenceNumberService $referenceNumberService
    ) {}

    /**
     * Submit a new document entry (metadata only, no file uploads).
     * Public - no login required.
     */
    public function submit(Request $request)
    {
        $isAuth = auth()->check();

        $rules = [
            'type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'routing_office_id' => 'required|integer',
        ];

        if (!$isAuth) {
            $rules['sender_first_name'] = ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s.\-]+$/'];
            $rules['sender_last_name']  = ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s.\-]+$/'];
            $rules['sender_contact'] = ['nullable', 'regex:/^09\d{9}$/'];
            $rules['sender_email'] = 'required|email|max:255';
        }

        $request->validate($rules, [
            'sender_first_name.regex' => 'First name may only contain letters, spaces, dots, and hyphens.',
            'sender_last_name.regex'  => 'Last name may only contain letters, spaces, dots, and hyphens.',
            'sender_contact.regex'    => 'Contact number must start with 09 and contain exactly 11 digits.',
            'sender_email.required'   => 'Email address is required so your submitted documents can be linked to your account later.',
        ]);

        if ($isAuth) {
            $authUser = auth()->user();
            if ($authUser->isRepresentative() && str_contains($authUser->name, ' - ')) {
                $parts = explode(' - ', $authUser->name, 2);
                $senderName = $parts[1];
            } else {
                $senderName = $authUser->name;
            }
            $senderEmail = $authUser->email;
            $senderContact = $request->sender_contact ?? null;
        } else {
            $senderName = trim($request->sender_first_name) . ' ' . trim($request->sender_last_name);
            $senderEmail = strtolower(trim((string) $request->sender_email));
            $senderContact = $request->sender_contact;

            $existingUser = User::where('email', $senderEmail)->first();
            if ($existingUser) {
                $message = 'This email is already registered. Please sign in to continue submitting documents.';

                if ($existingUser->isPending()) {
                    $message = 'This email is already registered and pending activation. Please activate your account and sign in.';
                } elseif ($existingUser->isSuspended()) {
                    $message = 'This email is already registered but currently deactivated. Please contact the administrator.';
                }

                return response()->json([
                    'success' => false,
                    'requires_login' => true,
                    'pending' => $existingUser->isPending(),
                    'suspended' => $existingUser->isSuspended(),
                    'message' => $message,
                ], 409);
            }
        }

        try {
            $destinationOffice = Office::query()
                ->active()
                ->whereRaw('COALESCE(is_school, false) = false')
                ->whereKey((int) $request->routing_office_id)
                ->first();

            if (!$destinationOffice) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected destination office is invalid or inactive.',
                ], 422);
            }

            $result = $this->trackingNumberService->generate();
            $referenceNumber = $this->referenceNumberService->generateUnique();

            $document = new Document([
                'submitted_to_office_id' => $destinationOffice->id,
                'subject' => $request->subject,
                'type' => $request->type,
                'sender_name' => $senderName,
                'sender_contact' => $senderContact,
                'sender_email' => $senderEmail,
                'description' => $request->description,
            ]);
            $document->tracking_number = $result['tracking_number'];
            $document->reference_number = $referenceNumber;
            $document->user_id = auth()->id();
            $document->current_office_id = null;
            $document->current_handler_id = null;
            $document->status = 'submitted';
            $document->last_action_at = now();
            $document->save();

            $recordsOfficeName = $this->getPhysicalSubmissionOfficeName();
            
            // Determine if submitter is office account or SuperAdmin
            $submitterIsOfficeAccount = false;
            $submitterIsSuperAdmin = false;
            if ($isAuth) {
                $authUser = auth()->user();
                $submitterIsOfficeAccount = $authUser->isOfficeAccount();
                $submitterIsSuperAdmin = $authUser->isSuperAdmin();
            }
            
            // Office-to-office submissions don't need Records routing
            $remarksText = ($submitterIsOfficeAccount || $submitterIsSuperAdmin)
                ? 'Document submitted for routing to ' . $destinationOffice->name . '.'
                : 'Document submitted online. Awaiting physical submission to ' . $recordsOfficeName . ' for routing to ' . $destinationOffice->name . '.';

            RoutingLog::create([
                'document_id' => $document->id,
                'performed_by' => auth()->id(),
                'from_office_id' => null,
                'to_office_id' => null,
                'action' => 'submitted',
                'status_after' => 'submitted',
                'remarks' => $remarksText,
            ]);

            // Determine if Records reminder should be shown based on submitter type and destination
            $showRecordsReminder = !($submitterIsOfficeAccount || $submitterIsSuperAdmin);

            return response()->json([
                'success' => true,
                'message' => 'Document submitted successfully!',
                'reference_number' => $document->reference_number,
                'tracking_number' => $document->tracking_number,
                'show_records_reminder' => $showRecordsReminder,
                'destination_office_name' => $destinationOffice->name,
                'details' => [
                    'sender_name'  => $document->sender_name,
                    'type'         => $document->type,
                    'subject'      => $document->subject,
                    'description'  => $document->description ?: 'No remarks provided',
                    'submitted_to' => $destinationOffice->name,
                    'date'         => $document->created_at->setTimezone('Asia/Manila')->format('M d, Y — h:i A'),
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Document submission failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit document. Please try again later.',
            ], 500);
        }
    }

    /**
     * Track a document by its tracking number - public.
     * Returns full routing log timeline.
     */
    public function track(Request $request)
    {
        return $this->trackLookupResponse($request, 15, false);
    }

    public function trackInternal(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !($user->isAdmin() || $user->isOfficeAccount())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to use the internal tracking endpoint.',
            ], 403);
        }

        return $this->trackLookupResponse($request, 5, true);
    }

    private function trackLookupResponse(Request $request, int $cacheSeconds, bool $includeInternalFields = false): JsonResponse
    {
        $lookupInput = $this->resolveTrackLookupInput($request);

        if ($lookupInput === '') {
            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'Tracking number is required.',
            ], 422);
        }

        $cacheKey = 'track-lookup:v4:' . ($includeInternalFields ? 'internal:' : 'public:') . $lookupInput;
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && isset($cached['status'], $cached['payload'])) {
            return response()->json($cached['payload'], (int) $cached['status']);
        }

        [$status, $payload] = $this->buildTrackLookupPayload($lookupInput, $includeInternalFields);

        if ($cacheSeconds > 0 && in_array($status, [200, 404], true)) {
            Cache::put($cacheKey, [
                'status' => $status,
                'payload' => $payload,
            ], now()->addSeconds($cacheSeconds));
        }

        return response()->json($payload, $status);
    }

    private function resolveTrackLookupInput(Request $request): string
    {
        $request->validate([
            'tracking_number' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'ref' => 'nullable|string',
        ]);

        return strtoupper(trim(strip_tags((string)($request->tracking_number ?: $request->reference_number ?: $request->ref))));
    }

    private function buildTrackLookupPayload(string $lookupInput, bool $includeInternalFields = false): array
    {
        $document = Document::with([
            'user.office',
            'submittedToOffice',
            'currentOffice',
            'currentHandler',
            'routingLogs.fromOffice',
            'routingLogs.toOffice',
            'routingLogs.performer',
        ])->where(function ($q) use ($lookupInput) {
            $q->whereRaw('UPPER(reference_number) = ?', [$lookupInput])
              ->orWhereRaw('UPPER(tracking_number) = ?', [$lookupInput]);
        })->first();

        if (!$document) {
            return [404, [
                'success' => false,
                'found' => false,
                'message' => 'Tracking number not found. Please check and try again.',
            ]];
        }

        $orderedLogs = $document->routingLogs
            ->sortBy(function ($log) {
                return sprintf('%s-%010d', $log->created_at?->format('YmdHis') ?? '00000000000000', (int) $log->id);
            })
            ->values();

        // Hide internal legacy action entries from public/user-facing timeline.
        $timelineLogs = $orderedLogs->reject(function ($log) {
            $action = strtolower(trim((string) $log->action));
            return str_contains($action, 'kumuha') || str_contains($action, 'stamp');
        })->values();

        if ($timelineLogs->isEmpty()) {
            $timelineLogs = $orderedLogs;
        }

        $timelineNow = now();
        $logCount = $timelineLogs->count();
        $submittedOfficeName = $document->submittedToOffice?->name ?: 'Records Section';

        $segments = [];

        for ($i = 0; $i < $logCount; $i++) {
            $log = $timelineLogs->get($i);
            $isSubmissionPending = $log->action === 'submitted' && $log->status_after === 'submitted';
            $officeId = null;
            if (!$isSubmissionPending) {
                if ($log->action === 'forwarded' && $log->from_office_id) {
                    $officeId = $log->from_office_id;
                } else {
                    $officeId = $log->to_office_id ?: $log->from_office_id;
                }
            }
            if (!$officeId) {
                continue;
            }

            if (empty($segments) || $segments[array_key_last($segments)]['office_id'] !== $officeId) {
                $segments[] = [
                    'office_id' => $officeId,
                    'start_index' => $i,
                    'end_index' => $i,
                ];
            } else {
                $segments[array_key_last($segments)]['end_index'] = $i;
            }
        }

        $officeNameMap = [];
        if (!empty($segments)) {
            $officeIds = array_values(array_unique(array_map(fn ($seg) => $seg['office_id'], $segments)));
            $officeNameMap = Office::query()
                ->whereIn('id', $officeIds)
                ->pluck('name', 'id')
                ->all();
        }

        $arrivalMetaByLogIndex = [];
        $terminalUserStatuses = ['completed', 'returned', 'cancelled', 'archived'];
        $shouldStopDurationNow = in_array((string) $document->status, $terminalUserStatuses, true);
        $terminalStopAt = $shouldStopDurationNow
            ? optional($timelineLogs->last())->created_at
            : null;
        $segmentCount = count($segments);
        for ($segIndex = 0; $segIndex < $segmentCount; $segIndex++) {
            $segment = $segments[$segIndex];
            $nextSegment = $segments[$segIndex + 1] ?? null;

            $startLog = $timelineLogs->get($segment['start_index']);
            $timeInAt = $startLog->created_at;
            $nextInAt = $nextSegment ? $timelineLogs->get($nextSegment['start_index'])->created_at : null;
            $segmentStopAt = $nextInAt ?: ($terminalStopAt ?: $timelineNow);
            $timeOutAt = $nextInAt ?: ($terminalStopAt ?: null);

            $officeDurationSeconds = max(0, $timeInAt->diffInSeconds($segmentStopAt));

            $arrivalMetaByLogIndex[$segment['start_index']] = [
                'office_name' => $officeNameMap[$segment['office_id']] ?? 'Office',
                'time_in_at' => $timeInAt,
                'time_out_at' => $timeOutAt,
                'office_duration_seconds' => $officeDurationSeconds,
                'between_offices_seconds' => null,
                'next_office_name' => $nextSegment
                    ? ($officeNameMap[$nextSegment['office_id']] ?? 'Next Office')
                    : null,
            ];
        }

        $formatPerformerName = function ($performer) {
            if (!$performer) {
                return null;
            }

            return str_contains($performer->name, ' - ')
                ? trim(substr($performer->name, strpos($performer->name, ' - ') + 3))
                : $performer->name;
        };

        $physicalSubmissionOfficeName = $this->getPhysicalSubmissionOfficeName();

        $logs = $timelineLogs->map(function ($log, $index) use ($submittedOfficeName, $arrivalMetaByLogIndex, $formatPerformerName, $physicalSubmissionOfficeName, $document) {
            $isSubmissionPending = $log->action === 'submitted' && $log->status_after === 'submitted';
            $arrivalMeta = $arrivalMetaByLogIndex[$index] ?? null;
            $officeDurationSeconds = $arrivalMeta['office_duration_seconds'] ?? null;
            $betweenOfficesSeconds = $arrivalMeta['between_offices_seconds'] ?? null;

            $remarks = $log->remarks;
            $displayToOffice = $isSubmissionPending ? ($log->toOffice?->name ?: $submittedOfficeName) : $log->toOffice?->name;
            
            $submitterIsOfficeOrAdmin = $document->isInternalOfficeSubmission();
            
            if ($isSubmissionPending) {
                $destinationOfficeName = $displayToOffice ?: 'the selected destination office';
                // Office-to-office submissions don't show "Awaiting physical submission"
                if ($submitterIsOfficeOrAdmin) {
                    $remarks = 'Document submitted for routing to ' . $destinationOfficeName . '.';
                } else {
                    $remarks = 'Document submitted online. Awaiting physical submission to ' . $physicalSubmissionOfficeName . ' for routing to ' . $destinationOfficeName . '.';
                }
            }

            $remarks = $this->normalizeTrackingRemarks($log, $remarks, $displayToOffice);
            $performedBy = $formatPerformerName($log->performer);
            if (!$performedBy && $log->action === 'submitted') {
                $performedBy = trim((string) $document->sender_name) ?: $formatPerformerName($document->user);
            }

            return [
                'id' => $log->id,
                'action' => $log->action,
                'action_label' => $log->actionLabel(),
                'action_label_with_office' => $log->actionLabelWithOffice(),
                'status_after' => $log->status_after,
                'from_office' => $isSubmissionPending ? null : $log->fromOffice?->name,
                'to_office' => $displayToOffice,
                'performed_by' => $performedBy,
                'remarks' => $remarks,
                'timestamp' => $log->created_at->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
                'office_name' => $arrivalMeta['office_name'] ?? null,
                'office_time_in' => $arrivalMeta
                    ? $arrivalMeta['time_in_at']->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A')
                    : null,
                'office_time_out' => ($arrivalMeta && $arrivalMeta['time_out_at'])
                    ? $arrivalMeta['time_out_at']->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A')
                    : null,
                'office_duration_human' => $officeDurationSeconds !== null
                    ? $this->formatDuration((int) $officeDurationSeconds)
                    : null,
                'office_duration_secs' => $officeDurationSeconds,
                'between_offices_human' => $betweenOfficesSeconds !== null
                    ? $this->formatDuration((int) $betweenOfficesSeconds)
                    : null,
                'between_offices_secs' => $betweenOfficesSeconds,
                'next_office' => $arrivalMeta['next_office_name'] ?? null,
            ];
        });

        $isSubmittedAwaitingAcceptance = $document->status === 'submitted';
        $currentOfficeName = $isSubmittedAwaitingAcceptance
            ? $submittedOfficeName
            : ($document->currentOffice?->name ?: $submittedOfficeName);

        $latestHandlerLog = $timelineLogs
            ->sortByDesc(function ($log) {
                return sprintf('%s-%010d', $log->created_at?->format('YmdHis') ?? '00000000000000', (int) $log->id);
            })
            ->first(function ($log) {
                return $log->performer && in_array($log->action, [
                    'processing',
                    'handoff',
                    'completed',
                    'for_pickup',
                    'returned',
                    'received',
                    'in_review',
                    'on_hold',
                ], true);
            });

        $currentHandlerName = $isSubmittedAwaitingAcceptance
            ? null
            : ($formatPerformerName($latestHandlerLog?->performer) ?: $formatPerformerName($document->currentHandler));

        if ($logs->isEmpty()) {
            $legacyTimestamp = $document->created_at->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A');
            $legacyOfficeName = $document->submittedToOffice?->name ?: $currentOfficeName;
            $legacyAction = $document->status === 'submitted' ? 'submitted' : 'processing';
            $legacyPerformedBy = $legacyAction === 'submitted'
                ? (trim((string) $document->sender_name) ?: $formatPerformerName($document->user))
                : $currentHandlerName;

            $logs = collect([[
                'id' => null,
                'action' => $legacyAction,
                'action_label' => 'Legacy Record',
                'status_after' => $document->status,
                'from_office' => null,
                'to_office' => $legacyOfficeName,
                'performed_by' => $legacyPerformedBy,
                'remarks' => 'Routing history is unavailable for this legacy document. Timeline logs were enabled after this record was created.',
                'timestamp' => $legacyTimestamp,
                'office_name' => $legacyOfficeName,
                'office_time_in' => $legacyTimestamp,
                'office_time_out' => null,
                'office_duration_human' => null,
                'office_duration_secs' => null,
                'between_offices_human' => null,
                'between_offices_secs' => null,
                'next_office' => null,
            ]]);
        }

        $documentPayload = [
            'id' => $document->id,
            'reference_number' => $document->reference_number ?: $document->tracking_number,
            'tracking_number' => $document->tracking_number,
            'subject' => $document->subject,
            'type' => $document->type,
            'description' => $document->description,
            'status' => $document->status,
            'status_label' => $document->statusLabel(),
            'status_color' => $document->statusColor(),
            'is_external' => $document->isExternal(),
            'sender_name' => $document->sender_name,
            'submitted_to_office' => $document->submittedToOffice?->name,
            'current_office' => $currentOfficeName,
            'current_handler' => $currentHandlerName,
            'last_action_at' => $document->last_action_at?->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
            'submitted_at' => $document->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
            'routing_logs' => $logs,
        ];

        if ($includeInternalFields) {
            $documentPayload['current_office_id'] = $document->current_office_id;
            $documentPayload['current_handler_id'] = $document->current_handler_id;
        }

        return [200, [
            'success' => true,
            'found' => true,
            'logs' => $logs,
            'document' => $documentPayload,
        ]];
    }

    private function getPhysicalSubmissionOfficeName(): string
    {
        $recordsOfficeName = Office::query()
            ->whereRaw('UPPER(code) = ?', ['RECORDS'])
            ->value('name');

        if (is_string($recordsOfficeName) && trim($recordsOfficeName) !== '') {
            return trim($recordsOfficeName);
        }

        return 'Records Section';
    }

    private function normalizeTrackingRemarks(RoutingLog $log, ?string $remarks, ?string $displayToOffice): ?string
    {
        $remarks = $remarks !== null ? trim($remarks) : null;

        if (strtolower((string) $log->action) !== 'processing') {
            return $remarks;
        }

        $legacyIctRemarks = [
            'document accepted by ict unit (super admin).',
            'document is now being processed by ict unit (super admin).',
        ];

        if ($remarks !== null && !in_array(strtolower($remarks), $legacyIctRemarks, true) && $remarks !== '') {
            return $remarks;
        }

        $fromOfficeName = $log->fromOffice?->name;
        $toOfficeName = $displayToOffice ?: $log->toOffice?->name;

        if ($fromOfficeName && $toOfficeName && strcasecmp($fromOfficeName, $toOfficeName) !== 0) {
            return "Document handed off from {$fromOfficeName} to {$toOfficeName}.";
        }

        if ($toOfficeName) {
            return "Document is now being processed at {$toOfficeName}.";
        }

        return 'Document is now being processed.';
    }

    /**
     * Generate QR code SVG for a tracking number.
     */
    public function qrCode(string $tracking)
    {
        $tracking = strtoupper(trim(strip_tags($tracking)));

        $document = Document::where(function ($q) use ($tracking) {
            $q->whereRaw('UPPER(tracking_number) = ?', [$tracking])
              ->orWhereRaw('UPPER(reference_number) = ?', [$tracking]);
        })->first();

        if (!$document) {
            abort(404);
        }

        $options = new QROptions([
            'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'     => QRCode::ECC_M,
            'scale'        => 10,
            'addQuietzone' => true,
            'imageBase64'  => false,
        ]);

        $receiveLookup = $document->reference_number ?: $document->tracking_number;
        $receiveUrl = url('/receive/' . $receiveLookup);
        $svg = (new QRCode($options))->render($receiveUrl);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;

        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;

        $minutes = intdiv($seconds, 60);
        $seconds %= 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }

        if (!$parts) {
            $parts[] = $seconds . 's';
        }

        return implode(' ', array_slice($parts, 0, 3));
    }
}

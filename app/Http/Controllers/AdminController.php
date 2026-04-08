<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Office;
use App\Models\RoutingLog;
use App\Mail\ActivationMail;
use App\Services\ActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(
        private ActivationService $activationService
    ) {}

    private function isRecordsOffice(?Office $office): bool
    {
        return $office && strtoupper((string) $office->code) === 'RECORDS';
    }

    private function applyOfficeQueueVisibility($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('user_id')
              ->orWhere('user_id', '!=', $user->id)
              ->orWhere('current_handler_id', $user->id);
        });
    }

    private function officeQueueBuilderForUser(User $user)
    {
        $office = $user->office;

        if (!$office) {
            return Document::with(['user', 'submittedToOffice', 'currentOffice'])
                ->whereRaw('1 = 0');
        }

        $isRecordsOffice = $this->isRecordsOffice($office);

        return $this->applyOfficeQueueVisibility(
            Document::with(['user', 'submittedToOffice', 'currentOffice']),
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
        })->when(
            $isRecordsOffice,
            fn ($q) => $q->whereNotIn('status', ['submitted', 'completed', 'returned', 'cancelled', 'archived']),
            fn ($q) => $q->whereNotIn('status', ['completed', 'returned', 'cancelled', 'archived'])
        );
    }

    private function officeQueueStatsForUser(User $user): array
    {
        $office = $user->office;

        if (!$office) {
            return [
                'active' => 0,
                'in_review' => 0,
                'completed' => 0,
            ];
        }

        $isRecordsOffice = $this->isRecordsOffice($office);

        $active = $this->applyOfficeQueueVisibility(Document::query(), $user)
            ->where(function ($q) use ($office, $isRecordsOffice) {
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
            ->when(
                $isRecordsOffice,
                fn ($q) => $q->whereIn('status', ['received', 'in_review']),
                fn ($q) => $q->whereIn('status', ['submitted', 'received', 'in_review'])
            )
            ->count();

        $inReview = $this->applyOfficeQueueVisibility(
            Document::where('current_office_id', $office->id),
            $user
        )->whereIn('status', ['received', 'in_review'])->count();

        $completed = $this->applyOfficeQueueVisibility(
            Document::where('submitted_to_office_id', $office->id),
            $user
        )->whereIn('status', ['completed', 'for_pickup'])->count();

        return [
            'active' => $active,
            'in_review' => $inReview,
            'completed' => $completed,
        ];
    }

    /**
     * Ensure the current user is an admin.
     */
    private function authorize()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }

    // ─── USER MANAGEMENT ───

    /**
     * List all users (excluding current admin).
     */
    public function users(Request $request)
    {
        $this->authorize();

        $query = User::with('office')->where('role', 'user');

        // Search filter
        if ($search = $request->get('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('representative_office_name', 'like', "%{$escaped}%")
                  ->orWhere('email', 'like', "%{$escaped}%")
                  ->orWhere('mobile', 'like', "%{$escaped}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if (in_array($status, ['active', 'pending', 'suspended'], true)) {
                $query->where('status', $status);
            }
        }

        $users = $query->withCount('documents')->latest()->paginate(15)->withQueryString();

        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('admin.users', [
            'user'    => Auth::user(),
            'users'   => $users,
            'offices' => $offices,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    /**
     * Update user (status, role).
     */
    public function updateUser(Request $request, $id)
    {
        $this->authorize();

        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $target = User::findOrFail($id);

        // Prevent editing self
        if ($target->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot modify your own account.'], 403);
        }

        // Prevent regular admin from modifying admin/superadmin accounts
        if ($target->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Cannot modify admin accounts.'], 403);
        }
        if ($target->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Cannot modify superadmin accounts.'], 403);
        }

        $request->validate([
            'status'    => 'sometimes|in:active,pending,suspended',
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|max:255|unique:users,email,' . $id,
            'mobile'    => 'sometimes|nullable|string|max:20',
            'office_id' => 'sometimes|nullable|exists:offices,id',
        ]);

        if ($request->has('status')) {
            $target->status = $request->status;
            if ($request->status === 'active' && !$target->activated_at) {
                $target->activated_at = now();
            }
        }

        if ($request->has('name')) {
            $newName = trim((string) $request->name);

            if ($target->account_type === 'representative' && !$target->office_id) {
                $newOfficeName = trim((string) $request->input('representative_office_name', ''));

                if ($newOfficeName === '' && str_contains($newName, ' - ')) {
                    [$newOfficeName, $newName] = explode(' - ', $newName, 2);
                    $newOfficeName = trim($newOfficeName);
                    $newName = trim($newName);
                }

                if ($newOfficeName !== '') {
                    $target->representative_office_name = $newOfficeName;
                }
            }

            $target->name = $newName;
        }
        if ($request->has('email'))     $target->email     = $request->email;
        if ($request->has('mobile'))    $target->mobile    = $request->mobile ?: null;
        if ($request->has('office_id')) {
            $target->office_id = $request->office_id ?: null;

            if ($target->office_id) {
                $target->representative_office_name = null;
            }
        }

        if (!$target->isDirty()) {
            return response()->json([
                'success' => false,
                'message' => 'No changes were made.',
            ]);
        }

        $target->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user'    => $target,
        ]);
    }

    /**
     * Delete a user account.
     */
    public function deleteUser($id)
    {
        $this->authorize();

        // Superadmins may only deactivate accounts, not permanently delete them.
        if (Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Super admins cannot delete accounts. Use deactivation instead.'], 403);
        }

        $target = User::findOrFail($id);

        if ($target->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 403);
        }

        if ($target->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete admin accounts.'], 403);
        }

        $target->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    // ─── DOCUMENT MANAGEMENT ───

    /**
     * List all documents.
     */
    public function documents(Request $request)
    {
        $this->authorize();

        if (Auth::user()?->isSuperAdmin()) {
            return redirect()->route('records.documents', array_filter([
                'search' => $request->get('search'),
                'status' => $request->get('status'),
            ], function ($value) {
                return $value !== null && $value !== '';
            }));
        }

        $query = Document::with('user');

        // Search filter
        if ($search = $request->get('search')) {
            $search = strip_tags($search);
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('reference_number', 'like', "%{$escaped}%")
                  ->orWhere('tracking_number', 'like', "%{$escaped}%")
                  ->orWhere('subject', 'like', "%{$escaped}%")
                  ->orWhere('sender_name', 'like', "%{$escaped}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if (array_key_exists($status, Document::FILTER_STATUSES)) {
                $query->where('status', $status);
            }
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'     => Document::count(),
            'processing' => Document::whereIn('status', ['received', 'in_review'])->count(),
            'completed' => Document::where('status', 'completed')->count(),
        ];

        return view('admin.documents', [
            'user'      => Auth::user(),
            'documents' => $documents,
            'stats'     => $stats,
            'filters'   => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    /**
     * Update document status.
     */
    public function updateDocument(Request $request, $id)
    {
        $this->authorize();

        $document = Document::findOrFail($id);

        $request->validate([
            'status' => 'required|in:submitted,received,in_review,completed,for_pickup,returned,archived',
        ]);

        $document->status = $request->status;
        $document->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Document status updated.',
            'document' => $document,
        ]);
    }

    /**
     * Delete a document.
     */
    public function deleteDocument($id)
    {
        $this->authorize();

        $document = Document::findOrFail($id);
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted.',
        ]);
    }

    // ─── OFFICE ACCOUNTS ───

    /**
     * List all office accounts.
     */
    public function officeAccounts(Request $request)
    {
        $this->authorize();

        $accounts = User::where('role', 'user')
            ->where('account_type', 'representative')
            ->whereNotNull('office_id')
            ->with('office')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('admin.offices', [
            'user'     => Auth::user(),
            'accounts' => $accounts,
            'offices'  => $offices,
        ]);
    }

    /**
     * Create a new office account.
     */
    public function createOfficeAccount(Request $request)
    {
        $this->authorize();

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email',
            'mobile'           => 'nullable|string|max:20',
            'office_id'        => 'required_without:new_office_name|nullable|exists:offices,id',
            'new_office_name'  => 'required_without:office_id|nullable|string|max:255',
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                // If a custom office name was provided, create or find the office.
                $officeId = $request->office_id;
                if ($request->filled('new_office_name') && !$officeId) {
                    $officeName = trim((string) $request->new_office_name);

                    $existingOffice = Office::whereRaw('LOWER(name) = ?', [strtolower($officeName)])->first();
                    if ($existingOffice) {
                        $officeId = $existingOffice->id;
                    } else {
                        $baseCode = strtoupper(Str::slug($officeName, '_'));
                        $baseCode = preg_replace('/[^A-Z0-9_]/', '', $baseCode);
                        if ($baseCode === '') {
                            $baseCode = 'OFFICE';
                        }
                        $baseCode = substr($baseCode, 0, 20);

                        $candidateCode = $baseCode;
                        $suffix = 1;
                        while (Office::where('code', $candidateCode)->exists()) {
                            $suffixText = (string) $suffix;
                            $maxBaseLength = max(1, 20 - strlen($suffixText) - 1);
                            $candidateCode = substr($baseCode, 0, $maxBaseLength) . '_' . $suffixText;
                            $suffix++;

                            if ($suffix > 999) {
                                throw new \RuntimeException('Unable to generate a unique office code.');
                            }
                        }

                        $office = Office::create([
                            'name' => $officeName,
                            'code' => $candidateCode,
                            'is_active' => true,
                        ]);

                        $officeId = $office->id;
                    }
                }

                $user = new User([
                    'name'         => $request->name,
                    'email'        => $request->email,
                    'mobile'       => $request->mobile ?: null,
                    'account_type' => 'representative',
                    'office_id'    => $officeId,
                    'password'     => Hash::make(Str::random(64)),
                ]);
                $user->status = 'pending';
                $user->role   = 'user';
                $user->save();

                return $user;
            });
        } catch (\Throwable $e) {
            \Log::error('Office account creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Could not create office account. Please try again.',
            ], 500);
        }

        $message = 'Office account created. Activation email sent to ' . $user->email . '.';
        try {
            $rawToken = $this->activationService->createToken($user);
            Mail::to($user->email)->send(new ActivationMail($user, $rawToken));
        } catch (\Throwable $e) {
            \Log::warning('Office account created but activation email failed for ' . $user->email . ': ' . $e->getMessage());
            $message = 'Office account created, but activation email could not be sent right now. Please use Resend Activation.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'user'    => $user->load('office'),
        ]);
    }

    /**
     * Delete an office account.
     */
    public function deleteOfficeAccount($id)
    {
        $this->authorize();

        $target = User::findOrFail($id);

        if ($target->account_type !== 'representative' || !$target->office_id) {
            return response()->json(['success' => false, 'message' => 'Not a valid office account.'], 422);
        }

        $target->delete();

        return response()->json([
            'success' => true,
            'message' => 'Office account deleted.',
        ]);
    }

    /**
     * Toggle reports dashboard access for an office account.
     */
    public function toggleReportsAccess($id)
    {
        $this->authorize();

        $target = User::findOrFail($id);

        if ($target->account_type !== 'representative' || !$target->office_id) {
            return response()->json(['success' => false, 'message' => 'Not a valid office account.'], 422);
        }

        $target->has_reports_access = !$target->has_reports_access;
        $target->save();

        $action = $target->has_reports_access ? 'granted' : 'revoked';

        return response()->json([
            'success' => true,
            'message' => "Reports access {$action} for {$target->name}.",
            'has_reports_access' => $target->has_reports_access,
        ]);
    }

    /**
     * Transfer an office account to a different office.
     */
    public function transferOfficeAccount(Request $request, $id)
    {
        $this->authorize();

        $request->validate([
            'office_id' => 'required|exists:offices,id',
        ]);

        $target = User::findOrFail($id);

        if ($target->account_type !== 'representative' || !$target->office_id) {
            return response()->json(['success' => false, 'message' => 'Not a valid office account.'], 422);
        }

        $newOfficeId = (int) $request->office_id;

        if ((int) $target->office_id === $newOfficeId) {
            return response()->json(['success' => false, 'message' => 'User is already assigned to this office.'], 422);
        }

        $oldOffice = $target->office;
        $newOffice = Office::findOrFail($newOfficeId);

        // Unassign this user from any in-progress documents at the old office.
        // The documents stay at the old office so another staff member can pick them up.
        $untagged = Document::where('current_handler_id', $target->id)
            ->where('current_office_id', $oldOffice->id)
            ->whereNotIn('status', ['completed', 'returned', 'cancelled', 'archived'])
            ->update(['current_handler_id' => null]);

        $target->office_id = $newOfficeId;
        $target->save();

        $extra = $untagged > 0
            ? " {$untagged} in-progress document(s) were untagged and returned to the {$oldOffice->name} queue."
            : '';

        return response()->json([
            'success' => true,
            'message' => "{$target->name} transferred from {$oldOffice->name} to {$newOffice->name}.{$extra}",
            'new_office_id' => $newOfficeId,
            'new_office_name' => $newOffice->name,
        ]);
    }

    // ─── ICT UNIT ───

    /**
     * ICT Unit — documents currently tagged to the superadmin (Sir Arthur).
     */
    public function ictDocuments(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Access restricted to Super Admin.');
        }

        $office = $user->office;
        $query = $office
            ? $this->officeQueueBuilderForUser($user)
            : Document::with(['user', 'submittedToOffice', 'currentOffice'])
                ->where('current_handler_id', $user->id)
                ->whereNotIn('status', ['completed', 'returned', 'cancelled', 'archived']);

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

        $status = trim((string) $request->get('status', ''));
        if ($status !== '' && array_key_exists($status, Document::FILTER_STATUSES)) {
            $query->where('status', $status);
        }

        $documents = $query->latest('last_action_at')->paginate(20)->withQueryString();

        $stats = $office
            ? $this->officeQueueStatsForUser($user)
            : [
                'active'    => Document::where('current_handler_id', $user->id)->whereNotIn('status', ['completed', 'for_pickup', 'archived', 'cancelled', 'returned'])->count(),
                'in_review' => Document::where('current_handler_id', $user->id)->whereIn('status', ['received', 'in_review'])->count(),
                'completed' => Document::where('current_handler_id', $user->id)->whereIn('status', ['completed', 'for_pickup'])->count(),
            ];

        return view('ict.index', compact('user', 'office', 'documents', 'stats', 'search', 'status'));
    }

    /**
     * ICT Unit — receive a document by reference number.
     */
    public function ictReceiveByReference(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $office = $user->office;

        $request->validate([
            'reference_number' => 'nullable|string|max:100',
            'tracking_number'  => 'nullable|string|max:100',
            'remarks'          => 'nullable|string|max:1000',
        ]);

        $lookupInput = strtoupper(trim(strip_tags((string)($request->reference_number ?: $request->tracking_number))));
        if ($lookupInput === '') {
            return response()->json(['success' => false, 'message' => 'Reference number is required.'], 422);
        }

        return DB::transaction(function () use ($lookupInput, $user, $office, $request) {
            $document = Document::with('currentHandler')->where(function ($q) use ($lookupInput) {
                $q->whereRaw('UPPER(reference_number) = ?', [$lookupInput])
                  ->orWhereRaw('UPPER(tracking_number) = ?', [$lookupInput]);
            })->lockForUpdate()->first();

        if (!$document) {
            return response()->json(['success' => false, 'message' => 'Reference number not found.'], 404);
        }

        if (in_array($document->status, ['completed', 'returned', 'cancelled', 'archived'], true)) {
            return response()->json(['success' => false, 'message' => 'This document is already closed and cannot be received.'], 422);
        }

        if ($office
            && (int) $document->current_office_id === (int) $office->id
            && in_array($document->status, ['received', 'in_review', 'for_pickup'], true)) {
            if ($document->current_handler_id && (int) $document->current_handler_id === (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'This document is already at your office and tagged to you (' . $document->statusLabel() . ').'], 422);
            }

            if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
                $previousHandlerName = optional($document->currentHandler)->name ?: 'another office user';
                $document->current_handler_id = $user->id;
                $document->last_action_at = now();
                $document->save();

                $officeName = $office->name ?? 'ICT Unit';
                RoutingLog::create([
                    'document_id'   => $document->id,
                    'performed_by'  => $user->id,
                    'from_office_id'=> $office->id,
                    'to_office_id'  => $office->id,
                    'action'        => 'handoff',
                    'status_after'  => $document->status,
                    'remarks'       => $request->input('remarks', "Internal handoff within {$officeName}: {$previousHandlerName} to {$user->name} via scan."),
                ]);

                return response()->json([
                    'success'          => true,
                    'message'          => 'Document handoff complete. You are now the handler.',
                    'status'           => $document->status,
                    'reference_number' => $document->reference_number ?: $document->tracking_number,
                    'tracking_number'  => $document->tracking_number,
                    'current_handler'  => $user->name,
                ]);
            }

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
        $toOfficeId   = $office?->id ?: $document->current_office_id;

        $document->current_handler_id = $user->id;
        if ($office) {
            $document->current_office_id = $office->id;
        }
        $document->status = 'in_review';
        $document->last_action_at = now();
        if (!$document->submitted_to_office_id && $office) {
            $document->submitted_to_office_id = $office->id;
        }
        $document->save();

        $defaultRemarks = $fromOfficeName
            ? "Document handed off from {$fromOfficeName} to " . ($office->name ?? 'ICT Unit') . '.'
            : 'Document is now being processed at ' . ($office->name ?? 'ICT Unit') . '.';

        RoutingLog::create([
            'document_id'   => $document->id,
            'performed_by'  => $user->id,
            'from_office_id'=> $fromOfficeId,
            'to_office_id'  => $toOfficeId,
            'action'        => 'processing',
            'status_after'  => 'in_review',
            'remarks'       => $request->input('remarks', $defaultRemarks),
        ]);

            $message = $fromOfficeName
                ? "Document accepted from {$fromOfficeName}."
                : 'Document is now being processed.';

            return response()->json([
                'success'          => true,
                'message'          => $message,
                'status'           => 'in_review',
                'reference_number' => $document->reference_number ?: $document->tracking_number,
                'tracking_number'  => $document->tracking_number,
                'current_office'   => $office?->name,
                'current_handler'  => $user->name,
            ]);
        }, 3);
    }

    /**
     * ICT Unit — accept a submitted document.
     */
    public function ictAccept(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $office = $user->office;

        $document = Document::findOrFail($id);

        if ($document->status !== 'submitted') {
            return response()->json(['success' => false, 'message' => 'Document cannot be accepted at its current status.'], 422);
        }

        if (!$office || (int) $document->submitted_to_office_id !== (int) $office->id) {
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

        $document->current_handler_id = $user->id;
        $document->current_office_id = $office->id;
        $document->status = 'in_review';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id'   => $document->id,
            'performed_by'  => $user->id,
            'from_office_id'=> $fromOfficeId,
            'to_office_id'  => $office->id,
            'action'        => 'processing',
            'status_after'  => 'in_review',
            'remarks'       => $request->input('remarks', 'Document is now being processed.'),
        ]);

        return response()->json([
            'success'         => true,
            'message'         => 'Document accepted successfully.',
            'status'          => 'in_review',
            'current_handler' => $user->name,
        ]);
    }

    /**
     * ICT Unit — live stats JSON.
     */
    public function ictStatsJson()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $user->office
            ? $this->officeQueueStatsForUser($user)
            : Cache::remember('ict_stats_' . $user->id, 15, function () use ($user) {
                $base = Document::where('current_handler_id', $user->id);
                return [
                    'active'    => (clone $base)->whereNotIn('status', ['completed', 'for_pickup', 'archived', 'cancelled', 'returned'])->count(),
                    'in_review' => (clone $base)->whereIn('status', ['received', 'in_review'])->count(),
                    'completed' => (clone $base)->whereIn('status', ['completed', 'for_pickup'])->count(),
                ];
            });

        return response()->json($stats);
    }
}

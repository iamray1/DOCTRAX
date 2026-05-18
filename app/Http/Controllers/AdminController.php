<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Office;
use App\Models\OfficeDocumentType;
use App\Models\RoutingLog;
use App\Mail\ActivationMail;
use App\Services\ActivationService;
use App\Services\DocumentStatusEmailService;
use App\Support\SubmissionNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private const TRANSFER_NON_BLOCKING_STATUSES = ['submitted', 'completed', 'returned', 'cancelled', 'archived'];

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
              ->orWhere('user_id', '!=', $user->id);
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

    private function officeQueueBuilderForUser(User $user, bool $includeCompleted = false)
    {
        $office = $user->office;

        if (!$office) {
            return Document::with(['user.office', 'submittedToOffice', 'currentOffice'])
                ->whereRaw('1 = 0');
        }

        $isRecordsOffice = $this->isRecordsOffice($office);

        $excludedStatuses = ['cancelled', 'archived'];
        if ($isRecordsOffice) {
            $excludedStatuses[] = 'submitted';
        }
        if (!$includeCompleted) {
            $excludedStatuses[] = 'completed';
        }

        return $this->applyOfficeQueueVisibility(
            Document::with(['user.office', 'submittedToOffice', 'currentOffice']),
            $user
        )->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
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
        })->whereNotIn('status', $excludedStatuses);
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
            ->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
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
        )->tap(fn ($query) => $this->excludeLatestOutboundHandoff($query, $office))
            ->whereIn('status', ['received', 'in_review'])->count();

        $completed = RoutingLog::query()
            ->where('performed_by', $user->id)
            ->where('action', 'completed')
            ->distinct('document_id')
            ->count('document_id');

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

    private function authorizeSuperAdmin()
    {
        if (!Auth::user() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }
    }

    private function schoolOfficesQuery()
    {
        return Office::query()->schools();
    }

    private function nonSchoolOfficesQuery()
    {
        return Office::query()->where(function ($query) {
            $query->where('is_school', false)
                ->orWhereNull('is_school');
        });
    }

    private function isInternalOfficeAccount(User $user): bool
    {
        if ($user->role !== 'user'
            || !$user->office_id
            || !in_array($user->account_type, ['office', 'representative'], true)) {
            return false;
        }

        $office = $user->relationLoaded('office')
            ? $user->office
            : Office::query()->select(['id', 'is_school'])->find($user->office_id);

        return $office && !$office->is_school;
    }

    private function normalizeSchoolName(string $name): string
    {
        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function generateSchoolCode(string $schoolName): string
    {
        $normalized = preg_replace('/\s+/', '_', $schoolName);
        $normalized = str_replace('-', '_', (string) $normalized);
        $normalized = preg_replace('/[^A-Z0-9_]/i', '', (string) $normalized);
        $base = strtoupper(trim((string) $normalized));
        $base = $base !== '' ? substr($base, 0, 16) : 'SCHOOL';
        $candidate = 'SCH_' . $base;
        $candidate = substr($candidate, 0, 20);
        $suffix = 1;

        while (Office::where('code', $candidate)->exists()) {
            $suffixText = (string) $suffix;
            $maxBaseLength = max(1, 20 - 4 - strlen($suffixText) - 1);
            $candidate = 'SCH_' . substr($base, 0, $maxBaseLength) . '_' . $suffixText;
            $suffix++;

            if ($suffix > 999) {
                throw new \RuntimeException('Unable to generate a unique school code.');
            }
        }

        return $candidate;
    }

    private function generateOfficeCode(string $officeName): string
    {
        $base = strtoupper(Str::slug($officeName, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', (string) $base);
        $base = $base !== '' ? substr($base, 0, 20) : 'OFFICE';

        $candidate = $base;
        $suffix = 1;

        while (Office::where('code', $candidate)->exists()) {
            $suffixText = (string) $suffix;
            $maxBaseLength = max(1, 20 - strlen($suffixText) - 1);
            $candidate = substr($base, 0, $maxBaseLength) . '_' . $suffixText;
            $suffix++;

            if ($suffix > 999) {
                throw new \RuntimeException('Unable to generate a unique office code.');
            }
        }

        return $candidate;
    }

    private function activeInProgressHandledDocumentsCount(User $target, int $officeId): int
    {
        return Document::query()
            ->where('current_handler_id', $target->id)
            ->where('current_office_id', $officeId)
            ->whereNotIn('status', self::TRANSFER_NON_BLOCKING_STATUSES)
            ->count();
    }

    private function activeInProgressSchoolTransferDocumentsCount(User $target, int $officeId): int
    {
        return Document::query()
            ->whereNotIn('status', self::TRANSFER_NON_BLOCKING_STATUSES)
            ->where(function ($query) use ($target, $officeId) {
                $query->where(function ($handled) use ($target, $officeId) {
                    $handled->where('current_handler_id', $target->id)
                        ->where('current_office_id', $officeId);
                })->orWhere('user_id', $target->id);
            })
            ->count();
    }

    private function schoolDependencyCounts(Office $school): array
    {
        $schoolName = strtolower(trim((string) $school->name));

        return [
            'assigned_users' => User::where('office_id', $school->id)->count(),
            'legacy_users' => User::where('account_type', 'representative')
                ->whereNull('office_id')
                ->whereRaw("LOWER(TRIM(COALESCE(representative_office_name, ''))) = ?", [$schoolName])
                ->count(),
            'submitted_documents' => Document::where('submitted_to_office_id', $school->id)->count(),
            'current_documents' => Document::where('current_office_id', $school->id)->count(),
            'routing_logs' => RoutingLog::where(function ($query) use ($school) {
                $query->where('from_office_id', $school->id)
                    ->orWhere('to_office_id', $school->id);
            })->count(),
        ];
    }

    private function schoolHasDependencies(array $counts): bool
    {
        return array_sum($counts) > 0;
    }

    private function schoolDeleteBlockedMessage(Office $school, array $counts): string
    {
        $parts = [];

        if ($counts['assigned_users'] > 0) {
            $parts[] = $counts['assigned_users'] . ' assigned user(s)';
        }
        if ($counts['legacy_users'] > 0) {
            $parts[] = $counts['legacy_users'] . ' legacy representative signup(s)';
        }
        if ($counts['submitted_documents'] > 0) {
            $parts[] = $counts['submitted_documents'] . ' submitted document record(s)';
        }
        if ($counts['current_documents'] > 0) {
            $parts[] = $counts['current_documents'] . ' in-queue document record(s)';
        }
        if ($counts['routing_logs'] > 0) {
            $parts[] = $counts['routing_logs'] . ' routing log record(s)';
        }

        if (!$parts) {
            return "School {$school->name} cannot be deleted right now.";
        }

        return "School {$school->name} cannot be deleted because it still has " . implode(', ', $parts) . ' attached.';
    }

    private function applyRepresentativeOfficeAssignment(User $target, int $newOfficeId): array
    {
        if ($target->account_type !== 'representative') {
            throw new \InvalidArgumentException('Only representative users can be assigned to a school.');
        }

        $oldOffice = $target->office_id
            ? ($target->office ?: Office::find($target->office_id))
            : null;
        $newOffice = Office::findOrFail($newOfficeId);

        if (!$newOffice->is_school) {
            throw new \InvalidArgumentException('Only school assignments are allowed here.');
        }

        if ($oldOffice && (int) $oldOffice->id === $newOfficeId) {
            return [
                'changed' => false,
                'old_office' => $oldOffice,
                'new_office' => $newOffice,
                'blocked' => 0,
            ];
        }

        $blocked = 0;
        if ($oldOffice) {
            $blocked = $this->activeInProgressSchoolTransferDocumentsCount($target, $oldOffice->id);

            if ($blocked > 0) {
                throw new \RuntimeException("Transfer blocked. {$target->name} has {$blocked} in-progress document(s) submitted by or assigned to this account. Finish or resolve those documents first.");
            }
        }

        $target->office_id = $newOfficeId;
        $target->representative_office_name = null;
        $target->setRelation('office', $newOffice);

        return [
            'changed' => true,
            'old_office' => $oldOffice,
            'new_office' => $newOffice,
            'blocked' => $blocked,
        ];
    }

    private function representativeOfficeAssignmentMessage(User $target, ?Office $oldOffice, Office $newOffice, int $blocked): string
    {
        if (!$oldOffice) {
            return "{$target->name} is now assigned to {$newOffice->name}.";
        }

        return "{$target->name} transferred from {$oldOffice->name} to {$newOffice->name}.";
    }

    // ─── USER MANAGEMENT ───

    /**
     * List all users (excluding current admin).
     */
    public function users(Request $request)
    {
        $this->authorize();

        $query = User::with('office')
            ->where('role', 'user')
            // Internal office accounts live on the Offices page, not Manage Users.
            ->where(function ($q) {
                $q->whereNull('account_type')
                    ->orWhere('account_type', '!=', 'office');
            })
            ->where(function ($q) {
                $q->whereNull('account_type')
                    ->orWhere('account_type', '!=', 'representative')
                    ->orWhereNull('office_id')
                    ->orWhereHas('office', function ($office) {
                        $office->where('is_school', true);
                    });
            });

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

        $offices = Office::query()
            ->schools()
            ->active()
            ->orderBy('name')
            ->get();

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

            if ($target->account_type === 'representative' && $target->office_id && str_contains($newName, ' - ')) {
                [, $newName] = explode(' - ', $newName, 2);
                $newName = trim($newName);
            }

            $target->name = $newName;
        }
        if ($request->has('email'))     $target->email     = $request->email;
        if ($request->has('mobile'))    $target->mobile    = $request->mobile ?: null;
        $officeMessage = null;
        if ($request->has('office_id')) {
            if ($target->account_type !== 'representative') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only representative users can be assigned to a school.',
                ], 422);
            }

            if ($request->filled('office_id')) {
                try {
                    $assignment = $this->applyRepresentativeOfficeAssignment($target, (int) $request->office_id);
                } catch (\InvalidArgumentException|\RuntimeException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], 422);
                }

                if ($assignment['changed']) {
                    $officeMessage = $this->representativeOfficeAssignmentMessage(
                        $target,
                        $assignment['old_office'],
                        $assignment['new_office'],
                        $assignment['blocked']
                    );
                }
            } else {
                $oldOfficeName = $target->representativeOfficeName();
                $target->office_id = null;

                if (!$target->representative_office_name && $oldOfficeName) {
                    $target->representative_office_name = $oldOfficeName;
                }
            }
        }

        if (!$target->isDirty()) {
            return response()->json([
                'success' => false,
                'message' => 'No changes were made.',
            ]);
        }

        $target->save();

        if ($target->status === 'active') {
            $this->activationService->linkGuestDocumentsForUser($target);
        }

        return response()->json([
            'success' => true,
            'message' => $officeMessage ?: 'User updated successfully.',
            'office_id' => $target->office_id,
            'office_name' => $target->representativeOfficeName(),
            'user'    => $target,
        ]);
    }

    public function schools(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = $this->schoolOfficesQuery()->withCount([
            'users',
            'documentsHeld as current_documents_count',
            'documentsSubmittedTo as submitted_documents_count',
        ]);

        $search = strip_tags(trim((string) $request->get('search', '')));
        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('code', 'like', "%{$escaped}%");
            });
        }

        $status = trim((string) $request->get('status', ''));
        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }
        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $schools = $query->orderBy('name')->paginate(15)->withQueryString();

        $normalizedSchoolNames = $schools->getCollection()
            ->map(fn (Office $school) => strtolower(trim((string) $school->name)))
            ->filter()
            ->unique()
            ->values();

        $legacyCounts = collect();
        if ($normalizedSchoolNames->isNotEmpty()) {
            $bindings = $normalizedSchoolNames->all();
            $placeholders = implode(',', array_fill(0, count($bindings), '?'));

            $legacyCounts = User::query()
                ->where('account_type', 'representative')
                ->whereNull('office_id')
                ->whereRaw("LOWER(TRIM(COALESCE(representative_office_name, ''))) IN ({$placeholders})", $bindings)
                ->selectRaw("LOWER(TRIM(COALESCE(representative_office_name, ''))) as office_name_norm, COUNT(*) as total")
                ->groupBy('office_name_norm')
                ->pluck('total', 'office_name_norm');
        }

        $schools->getCollection()->transform(function (Office $school) use ($legacyCounts) {
            $key = strtolower(trim((string) $school->name));
            $school->legacy_users_count = (int) ($legacyCounts[$key] ?? 0);
            return $school;
        });

        return view('admin.schools', [
            'user' => Auth::user(),
            'schools' => $schools,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function createSchool(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = $this->normalizeSchoolName((string) $request->input('name'));
        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'School name is required.'], 422);
        }

        if (Office::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
            return response()->json(['success' => false, 'message' => 'A school or office with that name already exists.'], 422);
        }

        $school = Office::create([
            'code' => $this->generateSchoolCode($name),
            'name' => $name,
            'is_active' => true,
            'is_school' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$school->name} was added to the available school list.",
            'school' => $school,
        ]);
    }

    public function updateSchool(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $school = $this->schoolOfficesQuery()->findOrFail($id);

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $newActiveState = (bool) $request->boolean('is_active');
        if ($school->is_active === $newActiveState) {
            return response()->json(['success' => false, 'message' => 'No changes were made.']);
        }

        if (!$newActiveState) {
            $assignedUsers = User::query()
                ->where('role', 'user')
                ->where('office_id', $school->id)
                ->count();

            if ($assignedUsers > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "{$school->name} still has {$assignedUsers} assigned representative(s). Transfer them first before deactivating this school.",
                    'assigned_users' => $assignedUsers,
                ], 422);
            }
        }

        $school->is_active = $newActiveState;

        $school->save();

        return response()->json([
            'success' => true,
            'message' => "{$school->name} " . ($newActiveState ? 'reactivated.' : 'deactivated.'),
            'school' => $school,
        ]);
    }

    public function deleteSchool($id)
    {
        $this->authorizeSuperAdmin();

        return response()->json([
            'success' => false,
            'message' => 'School deletion is disabled. Use deactivate instead.',
        ], 403);
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

        $query = User::where('role', 'user')
            ->whereIn('account_type', ['office', 'representative'])
            ->whereNotNull('office_id')
            ->whereHas('office', function ($office) {
                $office->where(function ($q) {
                    $q->where('is_school', false)
                        ->orWhereNull('is_school');
                });
            })
            ->with('office');

        $search = strip_tags(trim((string) $request->get('search', '')));
        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('email', 'like', "%{$escaped}%")
                    ->orWhere('mobile', 'like', "%{$escaped}%")
                    ->orWhereHas('office', function ($office) use ($escaped) {
                        $office->where('name', 'like', "%{$escaped}%")
                            ->orWhere('code', 'like', "%{$escaped}%");
                    });
            });
        }

        $accounts = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $offices = $this->nonSchoolOfficesQuery()
            ->active()
            ->orderBy('name')
            ->get();

        $officeStatusOffices = $this->nonSchoolOfficesQuery()
            ->withCount(['users as office_users_count' => function ($query) {
                $query->where('role', 'user')
                    ->whereIn('account_type', ['office', 'representative']);
            }])
            ->orderBy('name')
            ->get();

        $routingOfficesForTypeConfig = $this->nonSchoolOfficesQuery()
            ->orderByRaw("CASE WHEN UPPER(code) = 'RECORDS' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        $officeIdsForTypeConfig = $routingOfficesForTypeConfig->pluck('id');
        $officeDocumentTypesByOffice = [];
        if (Schema::hasTable('office_document_types')) {
            $officeDocumentTypesByOffice = OfficeDocumentType::query()
                ->whereIn('office_id', $officeIdsForTypeConfig)
                ->orderBy('name')
                ->get(['id', 'office_id', 'name', 'is_active'])
                ->groupBy('office_id')
                ->map(function ($rows) {
                    return $rows->map(function (OfficeDocumentType $row) {
                        return [
                            'id' => $row->id,
                            'office_id' => $row->office_id,
                            'name' => $row->name,
                            'is_active' => (bool) $row->is_active,
                        ];
                    })->values();
                })
                ->all();
        }

        $sanitizeDocumentTypeOptions = static function (array $options): array {
            return collect($options)
                ->filter(function ($option) {
                    return is_string($option)
                        && trim($option) !== ''
                        && strcasecmp(trim($option), 'Others') !== 0;
                })
                ->map(fn ($option) => trim($option))
                ->unique(fn ($option) => strtolower($option))
                ->values()
                ->all();
        };

        $defaultDocumentTypeOptions = $sanitizeDocumentTypeOptions((array) config('document-types.default', []));
        $documentTypeOverrides = (array) config('document-types.by_office_code', []);
        $baseDocumentTypeOptionsByOffice = $routingOfficesForTypeConfig
            ->mapWithKeys(function (Office $office) use ($documentTypeOverrides, $defaultDocumentTypeOptions, $sanitizeDocumentTypeOptions) {
                $officeCode = strtoupper(trim((string) $office->code));
                $options = $sanitizeDocumentTypeOptions((array) ($documentTypeOverrides[$officeCode] ?? []));

                if ($options === []) {
                    $options = $defaultDocumentTypeOptions;
                }

                return [(string) $office->id => $options];
            })
            ->all();

        return view('admin.offices', [
            'user' => Auth::user(),
            'accounts' => $accounts,
            'offices' => $offices,
            'officeStatusOffices' => $officeStatusOffices,
            'routingOfficesForTypeConfig' => $routingOfficesForTypeConfig,
            'officeDocumentTypesByOffice' => $officeDocumentTypesByOffice,
            'baseDocumentTypeOptionsByOffice' => $baseDocumentTypeOptionsByOffice,
            'filters' => [
                'search' => $search,
            ],
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

        if ($request->filled('office_id')) {
            $selectedOffice = $this->nonSchoolOfficesQuery()
                ->active()
                ->find((int) $request->office_id);

            if (!$selectedOffice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an active non-school office.',
                    'errors' => [
                        'office_id' => ['Please select an active non-school office.'],
                    ],
                ], 422);
            }
        }

        try {
            $user = DB::transaction(function () use ($request) {
                // If a custom office name was provided, create or find the office.
                $officeId = $request->office_id;
                if ($request->filled('new_office_name') && !$officeId) {
                    $officeName = trim((string) $request->new_office_name);

                    $existingOffice = $this->nonSchoolOfficesQuery()
                        ->whereRaw('LOWER(name) = ?', [strtolower($officeName)])
                        ->first();
                    if ($existingOffice) {
                        if (!$existingOffice->is_active) {
                            throw new \InvalidArgumentException('That office already exists but is inactive. Reactivate it from Office Status first.');
                        }

                        $officeId = $existingOffice->id;
                    } else {
                        $office = Office::create([
                            'name' => $officeName,
                            'code' => $this->generateOfficeCode($officeName),
                            'is_active' => true,
                            'is_school' => false,
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
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'new_office_name' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Office account creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Could not create office account. Please try again.',
            ], 500);
        }

        $message = 'Office account created. Activation email queued for ' . $user->email . '.';
        try {
            $rawToken = $this->activationService->createToken($user);
            Mail::to($user->email)->queue(new ActivationMail($user, $rawToken));
        } catch (\Throwable $e) {
            \Log::warning('Office account created but activation email could not be queued for ' . $user->email . ': ' . $e->getMessage());
            $message = 'Office account created, but activation email could not be queued right now. Please use Resend Activation.';
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

        if (!$this->isInternalOfficeAccount($target->loadMissing('office'))) {
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

        if (!$this->isInternalOfficeAccount($target->loadMissing('office'))) {
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

        $target = User::with('office')->findOrFail($id);

        if (!$this->isInternalOfficeAccount($target)) {
            return response()->json(['success' => false, 'message' => 'Not a valid office account.'], 422);
        }

        $newOfficeId = (int) $request->office_id;

        if ((int) $target->office_id === $newOfficeId) {
            return response()->json(['success' => false, 'message' => 'User is already assigned to this office.'], 422);
        }

        $newOffice = $this->nonSchoolOfficesQuery()
            ->active()
            ->find($newOfficeId);

        if (!$newOffice) {
            return response()->json([
                'success' => false,
                'message' => 'Please select an active non-school office.',
            ], 422);
        }

        $blocked = $this->activeInProgressHandledDocumentsCount($target, (int) $target->office_id);
        if ($blocked > 0) {
            return response()->json([
                'success' => false,
                'message' => "Transfer blocked. {$target->name} is currently handling {$blocked} in-progress document(s) at {$target->office->name}. Finish or hand off those documents first.",
            ], 422);
        }

        if ($target->account_type === 'office') {
            $target->account_type = 'representative';
        }

        $target->office_id = $newOfficeId;
        $target->save();

        return response()->json([
            'success' => true,
            'message' => 'Office account transferred to ' . $newOffice->name . '.',
            'new_office_id' => $newOffice->id,
            'new_office_name' => $newOffice->name,
        ]);
    }

    // Office catalog/status and document type options
    public function createOffice(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = preg_replace('/\s+/', ' ', trim((string) $request->input('name')));
        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Office name is required.'], 422);
        }

        $existingOffice = $this->nonSchoolOfficesQuery()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($existingOffice) {
            $message = $existingOffice->is_active
                ? 'That office already exists and is available when creating an office account.'
                : 'That office already exists but is inactive. Reactivate it from Office Status first.';

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $office = Office::create([
            'name' => $name,
            'code' => $this->generateOfficeCode($name),
            'is_active' => true,
            'is_school' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$office->name} was added to the office list.",
            'office' => $office,
        ]);
    }

    public function checkOfficeUsers($id)
    {
        $this->authorizeSuperAdmin();

        $office = $this->nonSchoolOfficesQuery()->findOrFail($id);
        $users = User::query()
            ->where('role', 'user')
            ->where('office_id', $office->id)
            ->whereIn('account_type', ['office', 'representative'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'status']);

        return response()->json([
            'success' => true,
            'office_id' => $office->id,
            'office_name' => $office->name,
            'has_users' => $users->isNotEmpty(),
            'users' => $users,
        ]);
    }

    public function updateOfficeStatus(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $office = $this->nonSchoolOfficesQuery()->findOrFail($id);
        $newActiveState = (bool) $request->boolean('is_active');

        if ($office->is_active === $newActiveState) {
            return response()->json(['success' => false, 'message' => 'No changes were made.']);
        }

        if (!$newActiveState) {
            $users = User::query()
                ->where('role', 'user')
                ->where('office_id', $office->id)
                ->whereIn('account_type', ['office', 'representative'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'status']);

            if ($users->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "{$office->name} still has assigned office account(s). Transfer them first.",
                    'has_users' => true,
                    'office_id' => $office->id,
                    'office_name' => $office->name,
                    'users' => $users,
                ], 422);
            }
        }

        $office->is_active = $newActiveState;
        $office->save();

        return response()->json([
            'success' => true,
            'message' => "{$office->name} " . ($newActiveState ? 'reactivated.' : 'deactivated.'),
            'office' => $office,
        ]);
    }

    public function createOfficeDocumentType(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'name' => 'required|string|max:255',
        ]);

        $office = $this->nonSchoolOfficesQuery()->find((int) $request->input('office_id'));
        if (!$office) {
            return response()->json(['success' => false, 'message' => 'Select a valid non-school office.'], 422);
        }

        $name = preg_replace('/\s+/', ' ', trim((string) $request->input('name')));
        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Document type name is required.'], 422);
        }
        if (strcasecmp($name, 'Others') === 0) {
            return response()->json(['success' => false, 'message' => 'The Others option is added automatically.'], 422);
        }

        $existing = OfficeDocumentType::query()
            ->where('office_id', $office->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($existing) {
            if (!$existing->is_active) {
                $existing->is_active = true;
                $existing->save();
            }

            return response()->json([
                'success' => true,
                'message' => "{$existing->name} is already available for {$office->name}.",
                'item' => $existing,
            ]);
        }

        $item = OfficeDocumentType::create([
            'office_id' => $office->id,
            'name' => $name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$item->name} added for {$office->name}.",
            'item' => $item,
        ]);
    }

    public function updateOfficeDocumentType(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $item = OfficeDocumentType::with('office')->findOrFail($id);
        if (!$item->office || $item->office->is_school) {
            return response()->json(['success' => false, 'message' => 'Document type is not attached to a valid office.'], 422);
        }

        $item->is_active = (bool) $request->boolean('is_active');
        $item->save();

        return response()->json([
            'success' => true,
            'message' => "{$item->name} " . ($item->is_active ? 'activated.' : 'deactivated.'),
            'item' => $item,
        ]);
    }

    // ICT UNIT

    /**
     * ICT Unit - documents currently tagged to the superadmin (Sir Arthur).
     */
    public function ictDocuments(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Access restricted to Super Admin.');
        }

        $office = $user->office;
        $status = trim((string) $request->get('queue_status', $request->get('status', '')));
        $statusForQuery = $status === 'processed' ? 'completed' : $status;
        $includeCompleted = $statusForQuery === 'completed';

        $query = $office
            ? $this->officeQueueBuilderForUser($user, $includeCompleted)
            : Document::with(['user.office', 'submittedToOffice', 'currentOffice'])
                ->where('current_handler_id', $user->id)
                ->whereNotIn('status', $includeCompleted ? ['cancelled', 'archived'] : ['completed', 'cancelled', 'archived']);

        $search = trim((string) $request->get('queue_search', $request->get('search', '')));
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

        if ($statusForQuery !== '' && array_key_exists($statusForQuery, Document::FILTER_STATUSES)) {
            $query->where('status', $statusForQuery);
        }

        $documents = $query->latest('last_action_at')->paginate(20)->withQueryString();

        $stats = $office
            ? $this->officeQueueStatsForUser($user)
            : [
                'active'    => Document::where('current_handler_id', $user->id)->whereNotIn('status', ['completed', 'for_pickup', 'archived', 'cancelled', 'returned'])->count(),
                'in_review' => Document::where('current_handler_id', $user->id)->whereIn('status', ['received', 'in_review'])->count(),
                'completed' => RoutingLog::query()
                    ->where('performed_by', $user->id)
                    ->where('action', 'completed')
                    ->distinct('document_id')
                    ->count('document_id'),
            ];
        $this->activationService->linkGuestDocumentsForUser($user);
        $submissionNotificationData = SubmissionNotifications::forUser($user);

        return view('ict.index', array_merge(
            compact('user', 'office', 'documents', 'stats', 'search', 'status'),
            $submissionNotificationData
        ));
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
            $document = Document::with(['currentHandler', 'user.office'])->where(function ($q) use ($lookupInput) {
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

        $submittedInternally = $document->isInternalOfficeSubmission();
        $isInitialOfficeIntake = is_null($document->current_office_id);
        $isRecordsReceiver = $this->isRecordsOffice($office);

        if ($isInitialOfficeIntake && !$submittedInternally && !$isRecordsReceiver) {
            return response()->json([
                'success' => false,
                'message' => 'This document must be received by Records Section first. ICT can receive it by scan after Records has accepted it.',
            ], 403);
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
            abort(403);
        }

        $office = $user->office;

        $stats = $office
            ? $this->officeQueueStatsForUser($user)
            : [
                'active'    => Document::where('current_handler_id', $user->id)->whereNotIn('status', ['completed', 'for_pickup', 'archived', 'cancelled', 'returned'])->count(),
                'in_review' => Document::where('current_handler_id', $user->id)->whereIn('status', ['received', 'in_review'])->count(),
                'completed' => RoutingLog::query()
                    ->where('performed_by', $user->id)
                    ->where('action', 'completed')
                    ->distinct('document_id')
                    ->count('document_id'),
            ];

        return response()->json($stats);
    }

    public function ictUpdateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status' => 'required|in:completed',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $document = Document::with(['user.office', 'currentHandler'])->findOrFail($id);
        $office = $user->office;

        if ($office && (int) $document->current_office_id !== (int) $office->id) {
            return response()->json(['success' => false, 'message' => 'This document is not at your office.'], 403);
        }

        if (!$office && (int) $document->current_handler_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'This document is not tagged to you.'], 403);
        }

        if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
            $handlerName = optional($document->currentHandler)->name ?: 'another admin';
            return response()->json([
                'success' => false,
                'message' => "This document is tagged to {$handlerName}.",
            ], 409);
        }

        if (!$document->current_handler_id) {
            $document->current_handler_id = $user->id;
        }

        if ($document->status === $request->status) {
            $statusLabel = Document::STATUSES[$request->status] ?? ucfirst(str_replace('_', ' ', $request->status));

            return response()->json([
                'success' => false,
                'message' => "This document is already {$statusLabel}.",
            ], 422);
        }

        if ($request->status === 'completed' && !$document->canCompleteTransactionFromCurrentStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Only For Pickup, For Return, or active documents can be ended.',
            ], 422);
        }

        $document->status = 'completed';
        $document->last_action_at = now();
        $document->save();

        RoutingLog::create([
            'document_id' => $document->id,
            'performed_by' => $user->id,
            'from_office_id' => null,
            'to_office_id' => $user->office_id,
            'action' => 'completed',
            'status_after' => 'completed',
            'remarks' => $request->remarks ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction ended. Document marked as Completed.',
            'status' => 'completed',
        ]);
    }

    public function ictBulkUpdateStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'integer',
            'status' => 'required|in:completed,for_pickup,returned',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $ids = collect($request->input('document_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Select at least one document.'], 422);
        }

        $office = $user->office;
        $newStatus = (string) $request->input('status');
        $remarks = $request->input('remarks') ?: null;
        $documents = Document::with(['user.office', 'currentHandler'])
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy('id');

        $updated = 0;
        $failures = [];
        $emailStatusChanges = [];

        DocumentStatusEmailService::beginBulkEmailCapture();
        try {
            foreach ($ids as $id) {
                $document = $documents->get($id);

                if (!$document) {
                    $failures[] = ['id' => $id, 'message' => 'Document not found.'];
                    continue;
                }

                $label = $document->reference_number ?: ($document->tracking_number ?: ('Document #' . $document->id));

                if ($office && (int) $document->current_office_id !== (int) $office->id) {
                    $failures[] = ['id' => $id, 'message' => "{$label} is not at your office."];
                    continue;
                }

                if (!$office && (int) $document->current_handler_id !== (int) $user->id) {
                    $failures[] = ['id' => $id, 'message' => "{$label} is not tagged to you."];
                    continue;
                }

                if ($document->current_handler_id && (int) $document->current_handler_id !== (int) $user->id) {
                    $handlerName = optional($document->currentHandler)->name ?: 'another admin';
                    $failures[] = ['id' => $id, 'message' => "{$label} is tagged to {$handlerName}."];
                    continue;
                }

                if ($document->isExternal() && !$user->isRecords() && $newStatus !== 'completed') {
                    $failures[] = ['id' => $id, 'message' => "{$label} can only have status updates from Records Section."];
                    continue;
                }

                if (in_array($document->status, ['completed', 'cancelled', 'archived'], true)) {
                    $failures[] = ['id' => $id, 'message' => "{$label} is already closed."];
                    continue;
                }

                if (!in_array($document->status, ['received', 'in_review', 'on_hold', 'for_pickup', 'returned'], true)) {
                    $failures[] = ['id' => $id, 'message' => "{$label} must be received before updating status."];
                    continue;
                }

                if ($document->status === $newStatus) {
                    $statusLabel = Document::STATUSES[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));
                    $failures[] = ['id' => $id, 'message' => "{$label} is already {$statusLabel}."];
                    continue;
                }

                if ($newStatus === 'completed' && !$document->canCompleteTransactionFromCurrentStatus()) {
                    $failures[] = ['id' => $id, 'message' => "{$label} must be For Pickup, For Return, or active before ending."];
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
                    'to_office_id' => $user->office_id,
                    'action' => $newStatus,
                    'status_after' => $newStatus,
                    'remarks' => $remarks,
                ]);

                $updated++;
                $emailStatusChanges[(int) $document->id] = $newStatus;
            }
        } finally {
            DocumentStatusEmailService::endBulkEmailCaptureAndSend($emailStatusChanges);
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
}

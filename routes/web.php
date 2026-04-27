<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\RecordsController;
use App\Models\Document;
use App\Models\Office;
use App\Models\OfficeDocumentType;

Route::get('/', function () {
    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return view('admin.home', compact('user'));
    }
    return view('welcome');
})->name('home');

Route::get('/about-us', function () {
    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return view('admin.about', compact('user'));
    }
    if ($user && $user->isOfficeAccount()) {
        return view('office.about', compact('user'));
    }
    if ($user) {
        return view('dashboard.about', compact('user'));
    }
    return view('about');
})->name('about');

Route::get('/contact-us', function () {
    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return view('admin.contact', compact('user'));
    }
    if ($user && $user->isOfficeAccount()) {
        return view('office.contact', compact('user'));
    }
    if ($user) {
        return view('dashboard.contact', compact('user'));
    }
    return view('contact');
})->name('contact');

Route::get('/help', function () {
    $user = auth()->user();
    if ($user) {
        return view('dashboard.help', compact('user'));
    }
    return view('help');
})->name('help');

Route::get('/track', function () {
    $myDocs = null;
    $user = auth()->user();
    if ($user) {
        $myDocs = $user->documents()->latest()->take(30)->get();
        // Serve admin sidebar version for admin/superadmin
        if ($user->isAdmin()) {
            return view('admin.track', compact('user', 'myDocs'));
        }
        // Serve the shared logged-in tracker for public-facing users and office accounts.
        return view('dashboard.track', compact('user', 'myDocs'));
    }
    return view('track.index', compact('myDocs'));
})->name('track');

Route::get('/submit', function () {
    $recordsOfficeName = Office::query()
        ->whereRaw('UPPER(code) = ?', ['RECORDS'])
        ->value('name') ?? 'Records Section';

    $routingOfficeOptions = Office::query()
        ->active()
        ->whereRaw('COALESCE(is_school, false) = false')
        ->orderByRaw("CASE WHEN UPPER(code) = 'RECORDS' THEN 0 ELSE 1 END")
        ->orderBy('name')
        ->get(['id', 'code', 'name']);

    $sanitizeDocumentTypeOptions = static function (array $options): array {
        return collect($options)
            ->filter(function ($option) {
                return is_string($option)
                    && trim($option) !== ''
                    && strcasecmp(trim($option), 'Others') !== 0;
            })
            ->map(function ($option) {
                return trim($option);
            })
            ->unique(function ($option) {
                return strtolower($option);
            })
            ->values()
            ->all();
    };

    $defaultDocumentTypeOptions = $sanitizeDocumentTypeOptions(
        (array) config('document-types.default', [])
    );

    $documentTypeOverrides = (array) config('document-types.by_office_code', []);

    $dbDocumentTypeRowsByOffice = collect();
    if (Schema::hasTable('office_document_types')) {
        $dbDocumentTypeRowsByOffice = OfficeDocumentType::query()
            ->whereIn('office_id', $routingOfficeOptions->pluck('id'))
            ->orderBy('name')
            ->get(['office_id', 'name', 'is_active'])
            ->groupBy('office_id');
    }

    $documentTypeOptionsByOffice = $routingOfficeOptions
        ->mapWithKeys(function ($office) use ($documentTypeOverrides, $defaultDocumentTypeOptions, $sanitizeDocumentTypeOptions, $dbDocumentTypeRowsByOffice) {
            $officeRows = $dbDocumentTypeRowsByOffice->get($office->id, collect());

            if ($officeRows->isNotEmpty()) {
                $officeOptions = $sanitizeDocumentTypeOptions(
                    $officeRows->where('is_active', true)->pluck('name')->all()
                );
            } else {
                $officeCode = strtoupper(trim((string) $office->code));
                $officeOptions = $sanitizeDocumentTypeOptions((array) ($documentTypeOverrides[$officeCode] ?? []));

                if ($officeOptions === []) {
                    $officeOptions = $defaultDocumentTypeOptions;
                }
            }

            return [(string) $office->id => $officeOptions];
        })
        ->all();

    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return view('admin.submit', compact('user', 'recordsOfficeName', 'routingOfficeOptions', 'documentTypeOptionsByOffice'));
    }

    if ($user && $user->isOfficeAccount()) {
        return view('office.submit', compact('recordsOfficeName', 'routingOfficeOptions', 'documentTypeOptionsByOffice'));
    }

    if ($user) {
        return view('dashboard.submit', compact('user', 'recordsOfficeName', 'routingOfficeOptions', 'documentTypeOptionsByOffice'));
    }

    return view('submit.index', compact('recordsOfficeName', 'routingOfficeOptions', 'documentTypeOptionsByOffice'));
})->name('submit');

Route::get('/login', function () {
    return view('auth.login');
})->middleware('no-cache')->name('login');

Route::get('/register', function () {
    $representativeSchools = Office::query()
        ->schools()
        ->active()
        ->orderBy('name')
        ->pluck('name');

    return view('auth.register', compact('representativeSchools'));
})->middleware('no-cache')->name('register');

// Activation routes
Route::get('/activate/{token}', [AuthController::class, 'showActivationForm'])->middleware('no-cache')->name('activation.form');
Route::post('/api/activate', [AuthController::class, 'activate'])->name('activation.activate');
Route::post('/api/resend-activation', [AuthController::class, 'resendActivation'])
    ->middleware('throttle:3,60')
    ->name('activation.resend');

// Forgot / Reset Password routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware('no-cache')->name('password.request');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->middleware('no-cache')->name('password.reset');
Route::post('/api/forgot-password', [AuthController::class, 'sendResetLink'])
    ->middleware('throttle:5,15')
    ->name('password.email');
Route::post('/api/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,60')
    ->name('password.update');

// QR code image (public - anyone with tracking number can view)
Route::get('/qr/{tracking}', [DocumentController::class, 'qrCode'])
    ->middleware('throttle:60,1')
    ->where('tracking', '[A-Za-z0-9\-]+');

// QR receive landing route (public entry with role-based behavior)
Route::get('/receive/{tracking}', function ($tracking) {
    $tracking = strtoupper(trim(strip_tags($tracking)));
    $user = auth()->user();
    $document = Document::query()
        ->select(['reference_number', 'tracking_number'])
        ->where(function ($q) use ($tracking) {
            $q->whereRaw('UPPER(reference_number) = ?', [$tracking])
              ->orWhereRaw('UPPER(tracking_number) = ?', [$tracking]);
        })
        ->first();
    $publicLookup = $document?->reference_number ?: $tracking;

    // Only office accounts and superadmin can access the receive screen.
    if ($user && ($user->isSuperAdmin() || $user->isOfficeAccount())) {
        $receiveEndpoint = $user->isSuperAdmin()
            ? '/api/ict/receive-by-reference'
            : '/api/office/documents/receive-by-reference';

        $backUrl = $user->isSuperAdmin() ? '/ict/documents' : '/office/dashboard';

        return view('office.receive', compact('user', 'tracking', 'receiveEndpoint', 'backUrl'));
    }

    // Everyone else (guests/regular users/non-office reps/admins) is redirected to tracking
    // using the user-facing reference number when available.
    return redirect('/track?ref=' . urlencode($publicLookup));
})->middleware('throttle:60,1')->where('tracking', '[A-Za-z0-9\-]+')->name('office.receive');

// Public API Routes
Route::post('/api/submit-document', [DocumentController::class, 'submit'])
    ->middleware('submit-throttle');
Route::match(['GET', 'POST'], '/api/track-document', [DocumentController::class, 'track'])
    ->middleware('track-throttle:public');
Route::match(['GET', 'POST'], '/api/internal/track-document', [DocumentController::class, 'trackInternal'])
    ->middleware(['auth', 'ensure-auth', 'track-throttle:internal']);
Route::post('/api/check-email', [AuthController::class, 'checkEmail'])
    ->middleware('throttle:10,1');
Route::post('/api/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');
Route::post('/api/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');
Route::post('/api/logout', [AuthController::class, 'logout'])
    ->middleware('throttle:10,1')
    ->name('logout');

// Authenticated routes
Route::middleware(['auth', 'ensure-auth', 'no-cache'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-documents', [DashboardController::class, 'myDocuments'])->name('my.documents')->middleware('throttle:60,1');
    Route::get('/api/my-stats', [DashboardController::class, 'userStatsJson'])->middleware('throttle:60,1');
    Route::get('/api/admin-stats', [DashboardController::class, 'adminStatsJson'])->middleware('throttle:60,1');
    Route::get('/api/office-stats', [RepresentativeController::class, 'officeStatsJson'])->middleware('throttle:60,1');
    Route::post('/api/documents/{reference}/cancel', [DashboardController::class, 'cancelDocument'])->middleware('throttle:10,1');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/api/profile', [ProfileController::class, 'update'])->middleware('throttle:10,1');
    Route::put('/api/profile/password', [ProfileController::class, 'changePassword'])->middleware('throttle:5,1');

    // ─── Office account routes ───────────────────────────────────────────────
    Route::get('/office/dashboard', [RepresentativeController::class, 'dashboard'])
        ->name('office.dashboard');
    Route::get('/representative/dashboard', [RepresentativeController::class, 'dashboard'])
        ->name('representative.dashboard');
    Route::get('/office/documents/{id}', [RepresentativeController::class, 'show'])
        ->name('office.document');
    Route::get('/representative/documents/{id}', [RepresentativeController::class, 'show'])
        ->name('representative.document');
    Route::post('/api/office/documents/{id}/accept', [RepresentativeController::class, 'accept'])->middleware('throttle:20,1');
    Route::post('/api/representative/documents/{id}/accept', [RepresentativeController::class, 'accept'])->middleware('throttle:20,1');
    Route::post('/api/office/documents/receive-by-reference', [RepresentativeController::class, 'receiveByReference'])->middleware('throttle:20,1');
    Route::post('/api/office/documents/{id}/status', [RepresentativeController::class, 'updateStatus'])->middleware('throttle:20,1');
    Route::post('/api/representative/documents/{id}/status', [RepresentativeController::class, 'updateStatus'])->middleware('throttle:20,1');
    Route::get('/office/search', [RepresentativeController::class, 'search'])
        ->name('office.search')->middleware('throttle:60,1');
    Route::get('/api/office/user-activity/{id}', [RepresentativeController::class, 'userActivityJson'])->middleware('throttle:30,1');
    Route::get('/office/users/{id}/export', [RepresentativeController::class, 'userActivityExport'])->name('office.user.export');

    // ─── Admin-only routes ───────────────────────────────────────────────────
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users')->middleware('throttle:60,1');
        Route::put('/api/admin/users/{id}', [AdminController::class, 'updateUser'])->middleware('throttle:20,1');
        Route::delete('/api/admin/users/{id}', [AdminController::class, 'deleteUser'])->middleware('throttle:10,1');

        Route::get('/admin/documents', [AdminController::class, 'documents'])->name('admin.documents')->middleware('throttle:60,1');
        Route::put('/api/admin/documents/{id}', [AdminController::class, 'updateDocument'])->middleware('throttle:20,1');
        Route::delete('/api/admin/documents/{id}', [AdminController::class, 'deleteDocument'])->middleware('throttle:10,1');

        Route::get('/admin/offices', [AdminController::class, 'officeAccounts'])->name('admin.offices')->middleware('throttle:30,1');
        Route::post('/api/admin/offices', [AdminController::class, 'createOfficeAccount'])->middleware('throttle:10,1');
        Route::post('/api/admin/offices/catalog', [AdminController::class, 'createOffice'])->middleware('throttle:10,1');
        Route::delete('/api/admin/offices/{id}', [AdminController::class, 'deleteOfficeAccount'])->middleware('throttle:10,1');
        Route::put('/api/admin/offices/{id}/reports', [AdminController::class, 'toggleReportsAccess'])->middleware('throttle:20,1');
        Route::put('/api/admin/offices/{id}/transfer', [AdminController::class, 'transferOfficeAccount'])->middleware('throttle:10,1');
        Route::post('/api/admin/offices/document-types', [AdminController::class, 'createOfficeDocumentType'])->middleware('throttle:20,1');
        Route::put('/api/admin/offices/document-types/{id}', [AdminController::class, 'updateOfficeDocumentType'])->middleware('throttle:20,1');
        Route::get('/api/admin/offices/{id}/check-users', [AdminController::class, 'checkOfficeUsers'])->middleware('throttle:20,1');
        Route::put('/api/admin/offices/{id}/status', [AdminController::class, 'updateOfficeStatus'])->middleware('throttle:20,1');
        Route::get('/admin/schools', [AdminController::class, 'schools'])->name('admin.schools')->middleware('throttle:30,1');
        Route::post('/api/admin/schools', [AdminController::class, 'createSchool'])->middleware('throttle:10,1');
        Route::put('/api/admin/schools/{id}', [AdminController::class, 'updateSchool'])->middleware('throttle:10,1');

        // ─── ICT Unit (SuperAdmin only) ───────────────────────────────────────────
        Route::get('/ict/documents', [AdminController::class, 'ictDocuments'])->name('ict.documents')->middleware('throttle:60,1');
        Route::post('/api/ict/receive-by-reference', [AdminController::class, 'ictReceiveByReference'])->middleware('throttle:20,1');
        Route::post('/api/ict/documents/{id}/accept', [AdminController::class, 'ictAccept'])->middleware('throttle:20,1');
        Route::post('/api/ict/documents/{id}/status', [AdminController::class, 'ictUpdateStatus'])->middleware('throttle:20,1');
        Route::get('/api/ict-stats', [AdminController::class, 'ictStatsJson'])->middleware('throttle:60,1');
    });

    // ─── Records Section & SuperAdmin routes ─────────────────────────────────
    Route::get('/records/documents', [RecordsController::class, 'index'])->name('records.documents')->middleware('throttle:60,1');
    Route::get('/records/documents/{id}', [RecordsController::class, 'show'])->name('records.document');
    Route::post('/api/records/documents/{id}/status', [RecordsController::class, 'updateStatus']);
    Route::get('/api/records-stats', [RecordsController::class, 'statsJson'])->middleware('throttle:60,1');
});

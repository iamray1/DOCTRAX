<?php

namespace App\Http\Controllers;

use App\Mail\ActivationMail;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private ActivationService $activationService
    ) {}

    /**
     * Register a new user (pending status, no real password).
     * Sends activation email with a secure one-time token.
     */
    public function register(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $accountType = $request->input('account_type') ?? 'individual';
        $name = trim((string) $request->input('name'));
        $representativeOfficeName = null;

        if ($accountType === 'representative') {
            $representativeOfficeName = trim((string) $request->input('office_name'));

            if ($representativeOfficeName !== '') {
                $prefix = $representativeOfficeName . ' - ';

                if (str_starts_with($name, $prefix)) {
                    $name = trim(substr($name, strlen($prefix)));
                } elseif (str_contains($name, ' - ')) {
                    [, $name] = explode(' - ', $name, 2);
                    $name = trim($name);
                }
            }
        }

        $request->merge([
            'name' => $name,
        ]);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'nullable|string|max:20',
            'account_type' => 'nullable|string|in:individual,representative',
            'office_name' => [
                Rule::requiredIf($accountType === 'representative'),
                'nullable',
                'string',
                'max:255',
                Rule::in(config('representative_offices', [])),
            ],
        ]);

        try {
            // Create the user in a transaction — email is sent outside so a
            // mail failure never prevents account creation.
            $user = DB::transaction(function () use ($request, $representativeOfficeName) {
                $user = new User([
                    'name'         => $request->name,
                    'email'        => $request->email,
                    'mobile'       => $request->mobile,
                    'account_type' => $request->account_type ?? 'individual',
                    'representative_office_name' => $representativeOfficeName,
                    'password'     => Hash::make(Str::random(64)), // placeholder — never usable
                ]);
                $user->status = 'pending';
                $user->save();
                return $user;
            });

            // Generate activation token (outside transaction — safe to retry)
            $rawToken = $this->activationService->createToken($user);

            // Send activation email; log but never crash on mail failure
            try {
                Mail::to($user->email)->send(new ActivationMail($user, $rawToken));
            } catch (\Exception $mailEx) {
                \Log::warning('Activation email could not be sent to ' . $user->email . ': ' . $mailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Account created. Check your email to set your password and activate your account.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
            ], 500);
        }
    }

    /**
     * Show the password setup page (from email link).
     */
    public function showActivationForm(string $token)
    {
        $activationToken = $this->activationService->findValidToken($token);

        if (!$activationToken) {
            // Check if user is already activated
            $hash = hash('sha256', $token);
            $expiredToken = \App\Models\ActivationToken::where('token_hash', $hash)->first();

            if ($expiredToken && $expiredToken->user && $expiredToken->user->isActive()) {
                return view('auth.activation-status', [
                    'status'  => 'already_active',
                    'message' => 'Your account is already activated. You can log in.',
                ]);
            }

            return view('auth.activation-status', [
                'status'  => 'invalid',
                'message' => 'This activation link is invalid or has expired.',
                'email'   => $expiredToken?->user?->email,
            ]);
        }

        return view('auth.set-password', [
            'token' => $token,
            'email' => $activationToken->user->email,
            'name'  => $activationToken->user->name,
        ]);
    }

    /**
     * Process password setup and activate the account.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // lowercase
                'regex:/[A-Z]/',      // uppercase
                'regex:/[0-9]/',      // digit
                'regex:/[!@#$%^&*]/', // special char
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character (!@#$%^&*).',
        ]);

        $activationToken = $this->activationService->findValidToken($request->token);

        if (!$activationToken) {
            return response()->json([
                'success' => false,
                'message' => 'This activation link is invalid or has expired.',
            ], 422);
        }

        $user = $this->activationService->activateUser(
            $activationToken,
            $request->password,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Your account has been activated! You can now log in.',
        ]);
    }

    /**
     * Resend activation email for a pending user.
     */
    public function resendActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with that email address.',
            ], 404);
        }

        if ($user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This account is already activated. You can log in.',
            ], 400);
        }

        if (!$this->activationService->canResend($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please wait before requesting another activation email.',
            ], 429);
        }

        $rawToken = $this->activationService->createToken($user);
        Mail::to($user->email)->send(new ActivationMail($user, $rawToken));

        return response()->json([
            'success' => true,
            'message' => 'A new activation email has been sent. Check your inbox.',
        ]);
    }

    /**
     * Check if an email exists in the system.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json(['exists' => false]);
        }

        // If user exists but is still pending, tell them to check email
        if ($user->isPending()) {
            return response()->json([
                'exists'  => true,
                'pending' => true,
                'message' => 'Your account is pending activation. Check your email to set your password.',
            ]);
        }

        // If the account has been suspended/deactivated
        if ($user->isSuspended()) {
            return response()->json([
                'exists'     => true,
                'suspended'  => true,
                'message'    => 'Your account has been deactivated. Please contact the administrator for assistance.',
            ]);
        }

        return response()->json(['exists' => true, 'pending' => false, 'suspended' => false]);
    }

    /**
     * Log in with email + password (only active accounts).
     *
     * Three-layer rate limiting:
     *  1. login_ip:{ip}        — 30 attempts / 60s  (global per-IP, blocks bots rotating emails)
     *  2. login:{email}:{ip}   — 5  attempts / 60s  (per email+IP combo, normal brute-force)
     *  3. login_email:{email}  — 15 attempts / 600s (per-email distributed, blocks multi-IP targeting one account)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Normalize email to lowercase before lookup and attempt
        $credentials['email'] = strtolower(trim($credentials['email']));

        $emailKey  = 'login:'        . $credentials['email'] . ':' . $request->ip();
        $ipKey     = 'login_ip:'     . $request->ip();
        $targetKey = 'login_email:'  . $credentials['email'];

        // Layer 1: global per-IP (catches bots rotating emails from one IP)
        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return $this->throttleResponse($seconds);
        }

        // Layer 2: per email+IP (normal brute-force from one machine)
        if (RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return $this->throttleResponse($seconds);
        }

        // Layer 3: per-email across all IPs (distributed attack on one account)
        if (RateLimiter::tooManyAttempts($targetKey, 15)) {
            $seconds = RateLimiter::availableIn($targetKey);
            return $this->throttleResponse($seconds);
        }

        // Check if user exists and is pending
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->isPending()) {
            return response()->json([
                'success' => false,
                'pending' => true,
                'message' => 'Your account is not yet activated. Check your email to set your password.',
            ], 403);
        }

        if ($user && $user->isSuspended()) {
            return response()->json([
                'success'    => false,
                'suspended'  => true,
                'message'    => 'Your account has been deactivated. Please contact the administrator for assistance.',
            ], 403);
        }

        if (Auth::attempt($credentials)) {
            // Clear all limiters on successful login
            RateLimiter::clear($emailKey);
            RateLimiter::clear($ipKey);
            RateLimiter::clear($targetKey);
            $request->session()->regenerate();

            $authed = Auth::user();
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user'    => [
                    'name'         => $authed->name,
                    'email'        => $authed->email,
                    'role'         => $authed->role,
                    'account_type' => $authed->account_type,
                ],
            ]);
        }

        // Hit all three limiters for each failed attempt
        RateLimiter::hit($emailKey,  60);   // 1 minute window
        RateLimiter::hit($ipKey,     60);   // 1 minute window
        RateLimiter::hit($targetKey, 600);  // 10 minute window

        $attemptsLeft = max(0, 5 - RateLimiter::attempts($emailKey));

        return response()->json([
            'success' => false,
            'message' => $attemptsLeft > 0
                ? 'Incorrect password. Please try again. (' . $attemptsLeft . ' ' . ($attemptsLeft === 1 ? 'attempt' : 'attempts') . ' remaining)'
                : 'Too many failed attempts. Please try again later.',
        ], 401);
    }

    /**
     * Standard throttle JSON response.
     */
    private function throttleResponse(int $seconds): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success'     => false,
            'throttled'   => true,
            'message'     => 'Too many login attempts. Please try again in ' . $seconds . ' ' . ($seconds === 1 ? 'second' : 'seconds') . '.',
            'retry_after' => $seconds,
        ], 429);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate the session and rotate the CSRF token.
        // `invalidate()` already flushes the session payload, so we avoid
        // doing extra cleanup work that slows down logout on the client.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        cookie()->queue(cookie()->forget(config('session.cookie')));
        cookie()->queue(cookie()->forget('XSRF-TOKEN'));

        return response()->json(['success' => true, 'redirect' => '/login'])
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
    }

    // ─────────────────────────────────────────────
    // Forgot / Reset Password
    // ─────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password-reset link to the given email.
     */
    public function sendResetLink(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate(['email' => 'required|email|max:255']);

        $user = User::where('email', $request->email)->first();

        // Fail fast so the frontend can keep the user on the form when the
        // email is unknown or the account is not active yet.
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found for that email address.',
                'errors' => [
                    'email' => ['No account found for that email address.'],
                ],
            ], 422);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Only active accounts can request a password reset link.',
                'errors' => [
                    'email' => ['Only active accounts can request a password reset link.'],
                ],
            ], 422);
        }

        // Hash a new raw token and upsert into password_reset_tokens
        $rawToken = Str::random(64);
        $hash     = hash('sha256', $rawToken);

        DB::table('password_reset_tokens')->upsert(
            [['email' => $user->email, 'token' => $hash, 'created_at' => now()]],
            ['email'],
            ['token', 'created_at']
        );

        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $rawToken));
        } catch (\Exception $e) {
            \Log::warning('Password reset email failed for ' . $user->email . ': ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'A reset link has been sent to your email address.',
        ]);
    }

    /**
     * Show the reset-password form (from email link).
     */
    public function showResetPassword(Request $request)
    {
        $token = $request->query('token', '');
        $email = $request->query('email', '');

        if (!$token || !$email) {
            return view('auth.reset-password', ['invalid' => true]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        // Validate token and 60-minute expiry
        if (
            !$record ||
            !hash_equals($record->token, hash('sha256', $token)) ||
            now()->diffInMinutes($record->created_at) > 60
        ) {
            return view('auth.reset-password', ['invalid' => true]);
        }

        return view('auth.reset-password', [
            'invalid' => false,
            'token'   => $token,
            'email'   => $email,
        ]);
    }

    /**
     * Process the password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', strtolower(trim($request->email)))
            ->first();

        if (
            !$record ||
            !hash_equals($record->token, hash('sha256', $request->token)) ||
            now()->diffInMinutes($record->created_at) > 60
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This reset link is invalid or has expired. Please request a new one.',
            ], 422);
        }

        $user = User::where('email', strtolower(trim($request->email)))->where('status', 'active')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for that email.',
            ], 404);
        }

        // Update password and delete the used token (single-use)
        DB::transaction(function () use ($user, $request) {
            $user->update(['password' => Hash::make($request->password)]);
            DB::table('password_reset_tokens')->where('email', strtolower(trim($request->email)))->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}

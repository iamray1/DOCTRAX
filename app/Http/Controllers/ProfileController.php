<?php

namespace App\Http\Controllers;

use App\Mail\AccountEmailVerificationCodeMail;
use App\Mail\AccountUpdateMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile page.
     * Routes office/representative users to sidebar-layout views.
     */
    public function show()
    {
        $user = Auth::user();

        // Admin account
        if ($user->isAdmin()) {
            return view('admin.profile', compact('user'));
        }

        // Admin-created office account (has office_id)
        if ($user->account_type === 'representative' && $user->office_id) {
            return view('office.profile', compact('user'));
        }

        // Self-registered representative (no office_id) uses the same profile
        // layout as regular users, with representative-specific labels handled
        // inside the shared view.
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update profile info (name, email, mobile).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:20',
        ]);

        $newName = $request->name;
        $newEmail = $request->email;
        $oldEmail = $user->email;

        $hasChanges = $user->name  !== $newName
               || $user->email !== $newEmail
                   || $user->mobile !== ($request->mobile ?: null);

        if (!$hasChanges) {
            return response()->json([
                'success' => true,
                'message' => 'No changes were made.',
                'user'    => [
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'mobile' => $user->mobile,
                ],
            ]);
        }

        if ($oldEmail !== $newEmail) {
            $verificationError = $this->emailChangeAuthorizationError($request, $user, $oldEmail);

            if ($verificationError) {
                return $verificationError;
            }
        }

        $user->name   = $newName;
        $user->email  = $newEmail;
        $user->mobile = $request->mobile;
        $user->save();

        if ($oldEmail !== $user->email) {
            try {
                Mail::to($user->email)->send(new AccountUpdateMail($user, 'email_changed', $oldEmail, $user->email));
            } catch (\Exception $e) {
                Log::warning('Account email change notice failed for ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user'    => [
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
            ],
        ]);
    }

    /**
     * Send a one-time code to the requested new email address.
     */
    public function sendEmailVerificationCode(Request $request)
    {
        $user = Auth::user();

        $rateKey = 'profile-email-code:' . $user->id . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return response()->json([
                'success' => false,
                'message' => 'Too many verification code requests. Please try again in ' . $seconds . ' ' . ($seconds === 1 ? 'second' : 'seconds') . '.',
                'retry_after' => $seconds,
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

        try {
            Mail::to($user->email)->send(new AccountEmailVerificationCodeMail($user, $code));
        } catch (\Exception $e) {
            Log::warning('Email change verification code failed for ' . $user->email . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Verification code could not be sent right now. Please try again later.',
            ], 500);
        }

        RateLimiter::hit($rateKey, 600);

        $request->session()->put('profile_email_change_verification', [
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to ' . $user->email . '.',
        ]);
    }

    /**
     * Verify the code sent to the current email before allowing an email edit.
     */
    public function verifyEmailVerificationCode(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'code' => 'required|string|regex:/^\d{6}$/',
        ], [
            'code.regex' => 'Enter the 6-digit verification code sent to your email.',
        ]);

        $pending = $request->session()->get('profile_email_change_verification');

        if (
            !is_array($pending)
            || (int) ($pending['user_id'] ?? 0) !== (int) $user->id
            || !hash_equals((string) ($pending['email'] ?? ''), (string) $user->email)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please request a new verification code.',
                'errors' => [
                    'code' => ['Please request a new verification code.'],
                ],
            ], 422);
        }

        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('profile_email_change_verification');

            return response()->json([
                'success' => false,
                'message' => 'Verification code expired. Please request a new code.',
                'errors' => [
                    'code' => ['Verification code expired. Please request a new code.'],
                ],
            ], 422);
        }

        if ((int) ($pending['attempts'] ?? 0) >= 5) {
            $request->session()->forget('profile_email_change_verification');

            return response()->json([
                'success' => false,
                'message' => 'Too many incorrect codes. Please request a new verification code.',
                'errors' => [
                    'code' => ['Too many incorrect codes. Please request a new verification code.'],
                ],
            ], 422);
        }

        if (!Hash::check((string) $request->input('code'), (string) ($pending['code_hash'] ?? ''))) {
            $pending['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;
            $request->session()->put('profile_email_change_verification', $pending);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
                'errors' => [
                    'code' => ['Invalid verification code.'],
                ],
            ], 422);
        }

        $request->session()->forget('profile_email_change_verification');
        $request->session()->put('profile_email_change_authorized', [
            'user_id' => $user->id,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email change verified. You can now edit your email address.',
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->password = $request->password; // auto-hashed via cast
        $user->save();

        try {
            Mail::to($user->email)->send(new AccountUpdateMail($user, 'password_changed'));
        } catch (\Exception $e) {
            Log::warning('Account password change notice failed for ' . $user->email . ': ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    private function emailChangeAuthorizationError(Request $request, User $user, string $oldEmail)
    {
        $verified = $request->session()->get('profile_email_change_authorized');

        if (
            !is_array($verified)
            || (int) ($verified['user_id'] ?? 0) !== (int) $user->id
            || !hash_equals((string) ($verified['email'] ?? ''), $oldEmail)
        ) {
            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Please verify your current email before changing it.',
                'errors' => [
                    'email' => ['Click Change email and verify your current email before saving.'],
                ],
            ], 422);
        }

        if ((int) ($verified['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('profile_email_change_authorized');

            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Email change verification expired. Please verify again.',
                'errors' => [
                    'email' => ['Click Change email and verify again before saving.'],
                ],
            ], 422);
        }

        $request->session()->forget('profile_email_change_authorized');

        return null;
    }
}

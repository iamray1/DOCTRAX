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
            'email_verification_code' => 'nullable|string|max:20',
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
            $verificationError = $this->emailChangeVerificationError($request, $user, $newEmail);

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

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $newEmail = $request->email;

        if ($newEmail === $user->email) {
            return response()->json([
                'success' => true,
                'message' => 'No email verification is needed.',
                'no_code_required' => true,
            ]);
        }

        $rateKey = 'profile-email-code:' . $user->id . ':' . sha1($newEmail) . ':' . $request->ip();

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
            Mail::to($newEmail)->send(new AccountEmailVerificationCodeMail($user, $code, $newEmail));
        } catch (\Exception $e) {
            Log::warning('Email change verification code failed for ' . $newEmail . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Verification code could not be sent right now. Please check the email address or try again later.',
            ], 500);
        }

        RateLimiter::hit($rateKey, 600);

        $request->session()->put('profile_email_change_verification', [
            'user_id' => $user->id,
            'email' => $newEmail,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to ' . $newEmail . '.',
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

    private function emailChangeVerificationError(Request $request, User $user, string $newEmail)
    {
        $code = trim((string) $request->input('email_verification_code', ''));

        if ($code === '') {
            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Please verify your new email address before saving.',
                'errors' => [
                    'email' => ['Request and enter the verification code sent to your new email address.'],
                ],
            ], 422);
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            return response()->json([
                'success' => false,
                'message' => 'Enter the 6-digit verification code sent to your new email.',
                'errors' => [
                    'email' => ['Enter the 6-digit verification code sent to your new email.'],
                ],
            ], 422);
        }

        $pending = $request->session()->get('profile_email_change_verification');

        if (
            !is_array($pending)
            || (int) ($pending['user_id'] ?? 0) !== (int) $user->id
            || !hash_equals((string) ($pending['email'] ?? ''), $newEmail)
        ) {
            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Please request a new verification code for this email address.',
                'errors' => [
                    'email' => ['Please request a new verification code for this email address.'],
                ],
            ], 422);
        }

        if ((int) ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('profile_email_change_verification');

            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Verification code expired. Please request a new code.',
                'errors' => [
                    'email' => ['Verification code expired. Please request a new code.'],
                ],
            ], 422);
        }

        if ((int) ($pending['attempts'] ?? 0) >= 5) {
            $request->session()->forget('profile_email_change_verification');

            return response()->json([
                'success' => false,
                'requires_email_verification' => true,
                'message' => 'Too many incorrect codes. Please request a new verification code.',
                'errors' => [
                    'email' => ['Too many incorrect codes. Please request a new verification code.'],
                ],
            ], 422);
        }

        if (!Hash::check($code, (string) ($pending['code_hash'] ?? ''))) {
            $pending['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;
            $request->session()->put('profile_email_change_verification', $pending);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
                'errors' => [
                    'email' => ['Invalid verification code.'],
                ],
            ], 422);
        }

        $request->session()->forget('profile_email_change_verification');

        return null;
    }
}

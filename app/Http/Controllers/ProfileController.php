<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user->name   = $newName;
        $user->email  = $newEmail;
        $user->mobile = $request->mobile;
        $user->save();

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

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\ActivationToken;
use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ActivationService
{
    /**
     * Token expires after 60 minutes.
     */
    private const TOKEN_EXPIRY_MINUTES = 60;

    /**
     * Create a new activation token for a user.
     * Invalidates any previous unused tokens.
     *
     * @return string The raw (unhashed) token to include in the email link.
     */
    public function createToken(User $user): string
    {
        // Invalidate all existing unused tokens for this user
        ActivationToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now(), 'expires_at' => now()]);

        // Generate a cryptographically secure random token
        $rawToken = Str::random(64);

        // Store only the hash in the database
        ActivationToken::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => Carbon::now()->addMinutes(self::TOKEN_EXPIRY_MINUTES),
        ]);

        return $rawToken;
    }

    /**
     * Find a valid activation token record by raw token string.
     *
     * @return ActivationToken|null
     */
    public function findValidToken(string $rawToken): ?ActivationToken
    {
        $hash = hash('sha256', $rawToken);

        $token = ActivationToken::where('token_hash', $hash)->first();

        if (!$token) {
            return null;
        }

        if (!$token->isValid()) {
            return null;
        }

        return $token;
    }

    /**
     * Activate the user: set password, mark token as used, update status.
     */
    public function activateUser(ActivationToken $token, string $password, ?string $ip = null): User
    {
        // Mark token as used
        $token->update(['used_at' => now()]);

        // Activate the user
        $user = $token->user;
        $user->password          = bcrypt($password);
        $user->status            = 'active';
        $user->email_verified_at = now();
        $user->activated_at      = now();
        $user->activation_ip     = $ip;
        $user->save();

        // Link any guest-submitted documents that used this email
        // before the user had an account.
        // Use query builder (not Eloquent update) since user_id is guarded.
        \Illuminate\Support\Facades\DB::table('documents')
            ->where('sender_email', $user->email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Check if a user can request a resend (rate limit: 3 per hour).
     */
    public function canResend(User $user): bool
    {
        $recentCount = ActivationToken::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentCount < 3;
    }
}

<?php

namespace App\Services;

use App\Models\ActivationToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        $this->linkGuestDocumentsForUser($user);

        return $user;
    }

    /**
     * Link guest-submitted documents to the authenticated account that owns
     * the same normalized email address.
     */
    public function linkGuestDocumentsForUser(User $user): int
    {
        if (!$user->isActive()) {
            return 0;
        }

        $email = strtolower(trim((string) $user->email));
        if ($email === '') {
            return 0;
        }

        $documents = DB::table('documents')
            ->whereNull('user_id')
            ->whereRaw('LOWER(TRIM(sender_email)) = ?', [$email])
            ->get(['id', 'current_office_id', 'submitted_to_office_id']);

        if ($documents->isEmpty()) {
            return 0;
        }

        DB::table('documents')
            ->whereIn('id', $documents->pluck('id')->all())
            ->update([
                'user_id' => $user->id,
                'updated_at' => now(),
            ]);

        Cache::forget('user_stats_' . $user->id);

        $officeIds = $documents
            ->flatMap(fn ($document) => [
                $document->current_office_id,
                $document->submitted_to_office_id,
            ])
            ->filter()
            ->unique();

        foreach ($officeIds as $officeId) {
            Cache::forget('office_stats_' . $officeId);
            Cache::forget('office_stats_user_' . $officeId . '_' . $user->id);
            Cache::forget('ict_stats_' . $user->id . '_office_' . $officeId);
        }
        Cache::forget('ict_stats_' . $user->id);

        return $documents->count();
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

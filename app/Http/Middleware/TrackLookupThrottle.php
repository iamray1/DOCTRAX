<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TrackLookupThrottle
{
    /**
     * Protect public tracking from bursts and repeated hammering
     * without blocking normal users who need multiple tries.
     */
    public function handle(Request $request, Closure $next, string $profile = 'public'): Response
    {
        $user = $request->user();
        $ip = (string) $request->ip();
        $userAgent = trim((string) $request->userAgent());
        $isInternalProfile = strtolower($profile) === 'internal';

        if ($isInternalProfile && (!$user || !($user->isAdmin() || $user->isOfficeAccount()))) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to use the internal tracking endpoint.',
            ], 403);
        }

        $actorId = $isInternalProfile
            ? ('staff:' . $user->id)
            : ($user
                ? ('user:' . $user->id)
                : ('guest:' . $ip . ':' . substr(hash('sha256', $userAgent !== '' ? $userAgent : 'unknown-agent'), 0, 12)));

        $lookupInput = strtoupper(trim(strip_tags((string) (
            $request->input('tracking_number')
            ?: $request->input('reference_number')
            ?: $request->input('ref')
        ))));

        if ($isInternalProfile) {
            $actorBurstLimit = 160;     // 10-second window
            $ipBurstLimit = 600;        // 10-second window
            $actorMinuteLimit = 2400;   // 60-second window
            $ipMinuteLimit = 10000;     // 60-second window
            $sameLookupLimit = 30;      // 15-second window
        } else {
            $isAuthenticated = (bool) $user;
            $actorBurstLimit = $isAuthenticated ? 40 : 18;       // 10-second window
            $ipBurstLimit = $isAuthenticated ? 120 : 45;         // 10-second window
            $actorMinuteLimit = $isAuthenticated ? 180 : 75;     // 60-second window
            $ipMinuteLimit = $isAuthenticated ? 600 : 180;       // 60-second window
            $sameLookupLimit = $isAuthenticated ? 15 : 8;        // 15-second window
        }

        $actorBurstKey = 'track-doc:actor:burst:' . $actorId;
        $ipBurstKey = 'track-doc:ip:burst:' . $ip;
        $actorMinuteKey = 'track-doc:actor:minute:' . $actorId;
        $ipMinuteKey = 'track-doc:ip:minute:' . $ip;

        $checks = [
            ['key' => $actorBurstKey, 'max' => $actorBurstLimit],
            ['key' => $ipBurstKey, 'max' => $ipBurstLimit],
            ['key' => $actorMinuteKey, 'max' => $actorMinuteLimit],
            ['key' => $ipMinuteKey, 'max' => $ipMinuteLimit],
        ];

        $lookupKey = null;
        if ($lookupInput !== '') {
            $lookupKey = 'track-doc:lookup:' . $actorId . ':' . substr(hash('sha256', $lookupInput), 0, 16);
            $checks[] = ['key' => $lookupKey, 'max' => $sameLookupLimit];
        }

        $retryAfter = 0;

        foreach ($checks as $check) {
            if (RateLimiter::tooManyAttempts($check['key'], $check['max'])) {
                $retryAfter = max($retryAfter, RateLimiter::availableIn($check['key']));
            }
        }

        if ($retryAfter > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Too many tracking attempts. Please wait a bit before trying again.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        RateLimiter::hit($actorBurstKey, 10);
        RateLimiter::hit($ipBurstKey, 10);
        RateLimiter::hit($actorMinuteKey, 60);
        RateLimiter::hit($ipMinuteKey, 60);

        if ($lookupKey !== null) {
            RateLimiter::hit($lookupKey, 15);
        }

        return $next($request);
    }
}

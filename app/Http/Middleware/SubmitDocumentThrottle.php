<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SubmitDocumentThrottle
{
    /**
     * Bulk-friendly submit throttling with abuse protection.
     *
     * Strategy:
     * - High per-user limits for authenticated users (supports bulk encoding).
     * - Reasonable per-IP limits for guests.
     * - Daily caps to reduce sustained scripted abuse.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ip = (string) $request->ip();

        $isAuthenticated = (bool) $user;
        $actorId = $isAuthenticated ? ('user:' . $user->id) : ('guest-ip:' . $ip);

        // "Practically unlimited" for legitimate bulk submissions,
        // while still blocking bot-like spikes and sustained scripted abuse.
        $actorBurstLimit = $isAuthenticated ? 800 : 60;      // 10-second window
        $ipBurstLimit = $isAuthenticated ? 3000 : 100;       // 10-second window
        $actorMinuteLimit = $isAuthenticated ? 4000 : 240;   // 60-second window
        $ipMinuteLimit = $isAuthenticated ? 20000 : 500;     // 60-second window
        $actorDayLimit = $isAuthenticated ? 200000 : 5000;   // 24-hour window
        $ipDayLimit = $isAuthenticated ? 1000000 : 20000;    // 24-hour window

        $actorBurstKey = 'submit-doc:actor:burst:' . $actorId;
        $ipBurstKey = 'submit-doc:ip:burst:' . $ip;
        $actorMinuteKey = 'submit-doc:actor:minute:' . $actorId;
        $ipMinuteKey = 'submit-doc:ip:minute:' . $ip;
        $actorDayKey = 'submit-doc:actor:day:' . $actorId;
        $ipDayKey = 'submit-doc:ip:day:' . $ip;

        $checks = [
            ['key' => $actorBurstKey, 'max' => $actorBurstLimit],
            ['key' => $ipBurstKey, 'max' => $ipBurstLimit],
            ['key' => $actorMinuteKey, 'max' => $actorMinuteLimit],
            ['key' => $ipMinuteKey, 'max' => $ipMinuteLimit],
            ['key' => $actorDayKey, 'max' => $actorDayLimit],
            ['key' => $ipDayKey, 'max' => $ipDayLimit],
        ];

        $retryAfter = 0;

        foreach ($checks as $check) {
            if (RateLimiter::tooManyAttempts($check['key'], $check['max'])) {
                $retryAfter = max($retryAfter, RateLimiter::availableIn($check['key']));
            }
        }

        if ($retryAfter > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Submit requests are temporarily limited to protect the system. Please wait a bit and try again.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        RateLimiter::hit($actorBurstKey, 10);
        RateLimiter::hit($ipBurstKey, 10);
        RateLimiter::hit($actorMinuteKey, 60);
        RateLimiter::hit($ipMinuteKey, 60);
        RateLimiter::hit($actorDayKey, 86400);
        RateLimiter::hit($ipDayKey, 86400);

        return $next($request);
    }
}

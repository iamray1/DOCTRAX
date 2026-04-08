<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Apply security headers to ALL responses (public + authenticated).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // XSS filter (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // HSTS — enforce HTTPS for 1 year (only effective over HTTPS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Referrer policy — send origin only on cross-origin requests
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy — this app uses SPA-style body swaps for authenticated pages,
        // so camera access must stay consistent for the whole document, not per swapped view.
        $user = $request->user();
        $allowCamera = $user && ($user->isAdmin() || $user->isOfficeAccount());
        $response->headers->set(
            'Permissions-Policy',
            $allowCamera
                ? 'camera=(self), microphone=(), geolocation=(), payment=()'
                : 'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // Content Security Policy — allow same-origin + trusted CDNs
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ]));

        return $response;
    }
}

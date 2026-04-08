<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Force redirect to login if not authenticated.
     * Prevents cached page access after logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Clear any remaining session data
            $request->session()->flush();
            $request->session()->regenerate();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        return $next($request);
    }
}

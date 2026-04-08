<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: dirname(__DIR__).'/routes/web.php',
        commands: dirname(__DIR__).'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Cloudflare Tunnel proxied requests (correct IP, HTTPS detection)
        $middleware->trustProxies(at: '*');

        // Global middleware — runs on every request
        $middleware->append(\App\Http\Middleware\RunSchedulerFallback::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'no-cache' => \App\Http\Middleware\NoCacheHeaders::class,
            'ensure-auth' => \App\Http\Middleware\EnsureAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

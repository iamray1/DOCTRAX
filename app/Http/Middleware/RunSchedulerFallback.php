<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunSchedulerFallback
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Fallback scheduler trigger for environments where OS cron/task scheduler is unavailable.
     * Runs at most once per interval and only after the response is sent.
     */
    public function terminate(Request $request, $response): void
    {
        if (app()->runningInConsole() || app()->environment('testing')) {
            return;
        }

        $enabled = filter_var((string) env('SELF_SCHEDULE_FALLBACK', 'true'), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return;
        }

        $intervalSeconds = max(30, (int) env('SELF_SCHEDULE_INTERVAL', 60));
        $lastRunKey = 'scheduler:fallback:last-run';
        $lockKey = 'scheduler:fallback:lock';
        $now = time();

        $lastRunAt = (int) Cache::get($lastRunKey, 0);
        if (($now - $lastRunAt) < $intervalSeconds) {
            return;
        }

        $lock = Cache::lock($lockKey, $intervalSeconds);
        if (!$lock->get()) {
            return;
        }

        try {
            // Re-check inside lock to prevent back-to-back runs across concurrent requests.
            $lastRunAt = (int) Cache::get($lastRunKey, 0);
            if (($now - $lastRunAt) < $intervalSeconds) {
                return;
            }

            Cache::put($lastRunKey, $now, now()->addMinutes(10));
            Artisan::call('schedule:run');
        } catch (\Throwable $e) {
            Log::warning('Scheduler fallback failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}

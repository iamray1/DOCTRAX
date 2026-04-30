<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-archive unprocessed documents hourly so stale submissions are cleaned up promptly.
Schedule::command('documents:archive-unprocessed')
    ->hourly()
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// Automatic pickup completion is intentionally disabled.
// Documents must remain in for_pickup/returned until the actual claim is confirmed.

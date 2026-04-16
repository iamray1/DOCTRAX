<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoCompletePickupDocuments extends Command
{
    protected $signature = 'documents:auto-complete-pickup {--days=3 : Retained for compatibility; automatic completion is disabled}';

    protected $description = 'Deprecated. for_pickup documents now remain open until an actual pickup is confirmed.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->warn('Automatic pickup completion is disabled.');
        $this->line("Documents in for_pickup stay there until someone confirms the actual claim. No status changes were made. Previous threshold: {$days} day(s).");

        return self::SUCCESS;
    }
}

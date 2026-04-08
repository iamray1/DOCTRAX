<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\RoutingLog;
use Illuminate\Console\Command;

class ArchiveUnprocessedDocuments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'documents:archive-unprocessed {--days=7 : Number of days before archiving}';

    /**
     * The console command description.
     */
    protected $description = 'Archive documents that remain in "submitted" status for more than the configured days.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days < 1) {
            $this->error('The --days option must be at least 1.');
            return self::INVALID;
        }

        $cutoff = now()->subDays($days);

        $baseQuery = Document::query()
            ->where('status', 'submitted')
            ->where(function ($q) use ($cutoff) {
                // Use last_action_at when available so archival is based on inactivity.
                $q->where(function ($sub) use ($cutoff) {
                    $sub->whereNotNull('last_action_at')
                        ->where('last_action_at', '<=', $cutoff);
                })->orWhere(function ($sub) use ($cutoff) {
                    $sub->whereNull('last_action_at')
                        ->where('created_at', '<=', $cutoff);
                });
            });

        $total = (clone $baseQuery)->count();
        if ($total === 0) {
            $this->info('No unprocessed documents found older than ' . $days . ' days.');
            return 0;
        }

        $count = 0;
        $baseQuery->orderBy('id')->chunkById(200, function ($documents) use (&$count, $days) {
            foreach ($documents as $document) {
                $document->status         = 'archived';
                $document->archived_at    = now();
                $document->last_action_at = now();
                $document->save();

                RoutingLog::create([
                    'document_id'    => $document->id,
                    'performed_by'   => null,
                    'from_office_id' => null,
                    'to_office_id'   => null,
                    'action'         => 'archived',
                    'status_after'   => 'archived',
                    'remarks'        => "Auto-archived: Document was not received within {$days} days of submission.",
                ]);

                $count++;
            }
        });

        $this->info("Archived {$count} unprocessed document(s).");

        return 0;
    }
}

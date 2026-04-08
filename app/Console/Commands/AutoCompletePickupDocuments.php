<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\RoutingLog;
use Illuminate\Console\Command;

class AutoCompletePickupDocuments extends Command
{
    protected $signature = 'documents:auto-complete-pickup {--days=3 : Days before auto-completing for_pickup documents}';

    protected $description = 'Auto-complete documents stuck in "for_pickup" status for more than 3 days (recipient did not confirm).';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $documents = Document::where('status', 'for_pickup')
            ->where('last_action_at', '<=', $cutoff)
            ->get();

        if ($documents->isEmpty()) {
            $this->info("No for_pickup documents older than {$days} days.");
            return 0;
        }

        $count = 0;
        foreach ($documents as $document) {
            $document->status         = 'completed';
            $document->last_action_at = now();
            $document->save();

            RoutingLog::create([
                'document_id'    => $document->id,
                'performed_by'   => null,
                'from_office_id' => $document->current_office_id,
                'to_office_id'   => null,
                'action'         => 'completed',
                'status_after'   => 'completed',
                'remarks'        => "Auto-completed: Recipient did not confirm pickup within {$days} days. Document assumed received.",
            ]);

            $count++;
        }

        $this->info("Auto-completed {$count} for_pickup document(s).");
        return 0;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('routing_logs')
            ->where('action', 'archived')
            ->whereNotNull('remarks')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $remarks = trim((string) $row->remarks);

                    if (!preg_match('/^Auto-archived: Document was not received within (\\d+) days of submission\\.?$/i', $remarks, $matches)) {
                        continue;
                    }

                    $days = $matches[1];
                    $updatedRemarks = 'Auto-archived: Document was not received within ' . $days . ' days of submission. Please submit again for routing.';

                    DB::table('routing_logs')
                        ->where('id', $row->id)
                        ->update(['remarks' => $updatedRemarks]);
                }
            }, 'id');
    }

    public function down(): void
    {
        DB::table('routing_logs')
            ->where('action', 'archived')
            ->whereNotNull('remarks')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $remarks = trim((string) $row->remarks);

                    if (!preg_match('/^Auto-archived: Document was not received within (\\d+) days of submission\\. Please submit again for routing\\.?$/i', $remarks, $matches)) {
                        continue;
                    }

                    $days = $matches[1];
                    $revertedRemarks = 'Auto-archived: Document was not received within ' . $days . ' days of submission.';

                    DB::table('routing_logs')
                        ->where('id', $row->id)
                        ->update(['remarks' => $revertedRemarks]);
                }
            }, 'id');
    }
};

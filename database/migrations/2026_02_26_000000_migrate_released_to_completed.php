<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate 'released' status to 'completed'.
     *
     * 'released' was previously used when a recipient confirmed pickup.
     * This has been unified into 'completed' for clarity.
     */
    public function up(): void
    {
        // Update documents still marked as 'released' → 'completed'
        DB::table('documents')
            ->where('status', 'released')
            ->update([
                'status'         => 'completed',
                'last_action_at' => DB::raw('last_action_at'), // keep existing timestamp
            ]);

        // Update routing log status_after
        DB::table('routing_logs')
            ->where('status_after', 'released')
            ->update(['status_after' => 'completed']);

        // Update routing log action (e.g. action = 'released')
        DB::table('routing_logs')
            ->where('action', 'released')
            ->update(['action' => 'completed']);
    }

    public function down(): void
    {
        // Not reversible; 'released' is deprecated
    }
};

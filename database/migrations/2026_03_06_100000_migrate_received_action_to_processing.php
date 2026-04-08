<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update old routing_logs action='received' → 'processing'
     * to match the new workflow where receive = automatic processing.
     */
    public function up(): void
    {
        $affected = DB::table('routing_logs')
            ->where('action', 'received')
            ->update(['action' => 'processing']);

        echo "routing_logs.action: received → processing — {$affected} rows updated.\n";
    }

    /**
     * Revert processing back to received.
     */
    public function down(): void
    {
        DB::table('routing_logs')
            ->where('action', 'processing')
            ->update(['action' => 'received']);
    }
};

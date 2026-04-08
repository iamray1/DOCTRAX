<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate historical "received" status to "in_review" across all tables.
     *
     * After the workflow change (auto-processing on receive), no new documents
     * will ever enter the "received" status. This migration cleans up old data
     * so that all records consistently use "in_review" instead.
     */
    public function up(): void
    {
        // Documents: received → in_review
        DB::table('documents')
            ->where('status', 'received')
            ->update(['status' => 'in_review']);

        // Routing logs: status_after received → in_review
        DB::table('routing_logs')
            ->where('status_after', 'received')
            ->update(['status_after' => 'in_review']);
    }

    /**
     * Reverse: in_review back to received (only affects rows that were
     * originally "received" — but since we can't distinguish them after the
     * forward migration, this is a best-effort rollback).
     */
    public function down(): void
    {
        // No reliable rollback — the forward migration is intentionally one-way.
        // Old "received" rows are indistinguishable from legitimately "in_review" rows.
    }
};

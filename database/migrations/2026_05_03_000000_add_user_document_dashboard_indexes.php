<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function ($table) {
            $table->index(['user_id', 'created_at', 'id'], 'documents_user_created_at_id_idx');
            $table->index(['user_id', 'status', 'created_at', 'id'], 'documents_user_status_created_at_id_idx');
        });

        DB::statement(
            'CREATE INDEX IF NOT EXISTS documents_guest_sender_email_idx ' .
            'ON documents ((LOWER(TRIM(sender_email)))) ' .
            'WHERE user_id IS NULL AND sender_email IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS documents_guest_sender_email_idx');

        Schema::table('documents', function ($table) {
            $table->dropIndex('documents_user_status_created_at_id_idx');
            $table->dropIndex('documents_user_created_at_id_idx');
        });
    }
};

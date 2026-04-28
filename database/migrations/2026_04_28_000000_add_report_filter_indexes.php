<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'documents_created_at_id_idx');
            $table->index(['last_action_at', 'id'], 'documents_last_action_at_id_idx');
            $table->index(['status', 'created_at', 'id'], 'documents_status_created_at_id_idx');
            $table->index(['status', 'last_action_at', 'id'], 'documents_status_last_action_id_idx');
            $table->index('type', 'documents_type_idx');
        });

        Schema::table('routing_logs', function (Blueprint $table) {
            $table->index(['from_office_id', 'document_id'], 'routing_logs_from_office_document_idx');
            $table->index(['to_office_id', 'document_id'], 'routing_logs_to_office_document_idx');
            $table->index(['performed_by', 'document_id'], 'routing_logs_performer_document_idx');
        });
    }

    public function down(): void
    {
        Schema::table('routing_logs', function (Blueprint $table) {
            $table->dropIndex('routing_logs_performer_document_idx');
            $table->dropIndex('routing_logs_to_office_document_idx');
            $table->dropIndex('routing_logs_from_office_document_idx');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_type_idx');
            $table->dropIndex('documents_status_last_action_id_idx');
            $table->dropIndex('documents_status_created_at_id_idx');
            $table->dropIndex('documents_last_action_at_id_idx');
            $table->dropIndex('documents_created_at_id_idx');
        });
    }
};

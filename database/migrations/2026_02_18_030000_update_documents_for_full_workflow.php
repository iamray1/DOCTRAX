<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Submitter contact info (public submission, no login required)
            $table->string('sender_contact', 20)->nullable()->after('sender_name');
            $table->string('sender_email')->nullable()->after('sender_contact');

            // Which office was this submitted TO (set on submit, never changes)
            $table->foreignId('submitted_to_office_id')->nullable()->after('user_id')
                  ->constrained('offices')->nullOnDelete();

            // Which office currently holds this document (changes on each forward)
            $table->foreignId('current_office_id')->nullable()->after('submitted_to_office_id')
                  ->constrained('offices')->nullOnDelete();

            // Drop the old string-based columns (keep sender_office for backward compat)
            // and add updated_status_at for timeline display
            $table->timestamp('last_action_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['submitted_to_office_id']);
            $table->dropForeign(['current_office_id']);
            $table->dropColumn(['sender_contact', 'sender_email', 'submitted_to_office_id', 'current_office_id', 'last_action_at']);
        });
    }
};

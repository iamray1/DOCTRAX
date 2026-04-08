<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes for production.
     * These columns appear in heavy WHERE / ORDER BY clauses across dashboards.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index('status');
            $table->index('current_office_id');
            $table->index('submitted_to_office_id');
            $table->index('current_handler_id');
            $table->index('last_action_at');
        });

        Schema::table('routing_logs', function (Blueprint $table) {
            $table->index('from_office_id');
            $table->index('to_office_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('account_type');
            $table->index('role');
            $table->index('office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['current_office_id']);
            $table->dropIndex(['submitted_to_office_id']);
            $table->dropIndex(['current_handler_id']);
            $table->dropIndex(['last_action_at']);
        });

        Schema::table('routing_logs', function (Blueprint $table) {
            $table->dropIndex(['from_office_id']);
            $table->dropIndex(['to_office_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_type']);
            $table->dropIndex(['role']);
            $table->dropIndex(['office_id']);
        });
    }
};

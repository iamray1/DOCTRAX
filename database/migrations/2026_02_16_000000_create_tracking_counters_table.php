<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tracking counters table — stores the last used sequence number
     * per (office_code, year, month). Used with row-level locking
     * (SELECT ... FOR UPDATE) to guarantee concurrency-safe,
     * lifetime-unique tracking numbers.
     */
    public function up(): void
    {
        Schema::create('tracking_counters', function (Blueprint $table) {
            $table->id();
            $table->string('office_code', 10);       // e.g. CSJDM, REG3
            $table->unsignedSmallInteger('year');      // e.g. 2026
            $table->unsignedTinyInteger('month');      // 1–12
            $table->unsignedInteger('last_sequence')   // last used number (0 = none yet)
                  ->default(0);
            $table->timestamps();

            // Composite unique: one counter row per office+year+month
            $table->unique(['office_code', 'year', 'month'], 'tracking_counters_unique');
        });

        // Add office_code column to documents table for future multi-office support
        Schema::table('documents', function (Blueprint $table) {
            $table->string('office_code', 10)->default('CSJDM')->after('tracking_number');
        });

        // Add a composite index on documents for fast lookups
        // tracking_number already has a unique index from the original migration
        DB::statement('CREATE INDEX IF NOT EXISTS idx_documents_office_code ON documents (office_code)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_documents_office_code');
            $table->dropColumn('office_code');
        });

        Schema::dropIfExists('tracking_counters');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * routing_logs — immutable audit trail of every action on a document.
     *
     * Actions: submitted | received | forwarded | in_review
     *          | completed | released | returned | cancelled
     */
    public function up(): void
    {
        Schema::create('routing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();

            // Who performed the action (null = public/anonymous)
            $table->foreignId('performed_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('from_office_id')->nullable()
                  ->constrained('offices')->nullOnDelete();

            $table->foreignId('to_office_id')->nullable()
                  ->constrained('offices')->nullOnDelete();

            // The action taken — mirrors document status
            $table->string('action', 30);      // submitted|received|forwarded|etc.

            // What the document status was set TO after this action
            $table->string('status_after', 30);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('document_id');
            $table->index('performed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_logs');
    }
};

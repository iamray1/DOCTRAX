<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique(); // The unique code
            $table->string('subject');
            $table->string('type')->nullable(); // e.g., Memo, Letter
            $table->string('status')->default('received'); // received, forwarded, completed
            $table->string('sender_name')->nullable();
            $table->string('sender_office')->nullable();
            $table->string('recipient_office')->nullable();
            $table->text('description')->nullable();
            $table->json('files')->nullable(); // For attachments
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->string('name', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['office_id', 'name'], 'office_document_types_unique');
            $table->index(['office_id', 'is_active'], 'office_document_types_office_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_document_types');
    }
};

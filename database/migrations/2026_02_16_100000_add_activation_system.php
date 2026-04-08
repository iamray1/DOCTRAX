<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add status + mobile to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('password');
            $table->string('mobile', 20)->nullable()->after('email');
            $table->string('account_type', 20)->default('individual')->after('status');
            $table->timestamp('activated_at')->nullable()->after('email_verified_at');
            $table->string('activation_ip', 45)->nullable()->after('activated_at');
            $table->index('status');
        });

        // Activation tokens table
        Schema::create('activation_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_hash', 64); // SHA-256 hash of the raw token
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('token_hash');
            $table->index(['user_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'mobile', 'account_type', 'activated_at', 'activation_ip']);
        });
    }
};

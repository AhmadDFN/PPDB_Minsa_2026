<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('provider_code', 50);
            $table->string('channel_name', 120);
            $table->string('sender_identifier', 40)->nullable();
            $table->string('sender_name', 120)->nullable();
            $table->string('connection_status', 30)->default('unknown');
            $table->timestamp('last_validated_at')->nullable();
            $table->string('last_connection_error_code', 80)->nullable();
            $table->timestamp('last_connection_error_at')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['provider_code', 'connection_status']);
        });

        Schema::create('communication_channel_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('communication_channel_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('credential_ciphertext');
            $table->unsignedInteger('credential_version')->default(1);
            $table->timestamps();
        });

        Schema::create('communication_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('active_channel_id')->nullable()->constrained('communication_channels')->restrictOnDelete();
            $table->boolean('database_provider_mode_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('communication_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('communication_channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 80);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_audit_logs');
        Schema::dropIfExists('communication_settings');
        Schema::dropIfExists('communication_channel_credentials');
        Schema::dropIfExists('communication_channels');
    }
};

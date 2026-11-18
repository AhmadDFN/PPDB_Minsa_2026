<?php

declare(strict_types=1);

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
        Schema::create('registration_reopen_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->dateTime('opened_at');
            $table->dateTime('expires_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('close_reason')->nullable();
            $table->timestamps();

            $table->index(['registration_id', 'closed_at', 'expires_at'], 'registration_reopen_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_reopen_windows');
    }
};

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
        Schema::table('registrations', function (Blueprint $table): void {
            $table->unsignedInteger('queue_sequence')->nullable()->after('registration_number');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->foreignId('interview_batch_id')->nullable()->after('verified_by')->constrained('interview_batches')->nullOnDelete();

            $table->index(['admission_period_id', 'queue_sequence'], 'registrations_period_queue_index');
            $table->index('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex('registrations_period_queue_index');
            $table->dropIndex(['verified_at']);
            $table->dropConstrainedForeignId('interview_batch_id');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['verified_at', 'queue_sequence']);
        });
    }
};

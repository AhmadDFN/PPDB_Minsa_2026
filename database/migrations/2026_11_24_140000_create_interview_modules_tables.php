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
        // 1. Master Jadwal & Kuota Harian Wawancara
        Schema::create('interview_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->unsignedInteger('batch_number');
            $table->string('title', 100);
            $table->date('interview_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('quota')->default(70);
            $table->unsignedInteger('sequence_start');
            $table->unsignedInteger('sequence_end');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['admission_period_id', 'is_active']);
            $table->index(['sequence_start', 'sequence_end']);
        });

        // 2. Master Tahap Wawancara (Customizable)
        Schema::create('interview_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_order');
            $table->string('code', 30);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('program_scope', 20)->default('ALL'); // 'ALL' or 'UNGGULAN'
            $table->string('required_role', 50); // 'interviewer_stage_1', 'interviewer_stage_2', 'interviewer_stage_3'
            $table->boolean('is_confidential')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['admission_period_id', 'stage_order'], 'interview_stages_period_order_unique');
            $table->index(['admission_period_id', 'is_active']);
        });

        // 3. Master Poin Penilaian Wawancara (Customizable)
        Schema::create('interview_criteria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('interview_stage_id')->constrained('interview_stages')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_score')->default(0);
            $table->unsignedTinyInteger('max_score')->default(100);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['interview_stage_id', 'is_active', 'sort_order'], 'interview_criteria_stage_sort_index');
        });

        // 4. Sesi Wawancara per Siswa per Tahap
        Schema::create('interview_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('interview_stage_id')->constrained('interview_stages')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('IN_PROGRESS'); // 'IN_PROGRESS', 'COMPLETED'
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('total_score')->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('last_edit_reason')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'interview_stage_id'], 'interview_sessions_reg_stage_unique');
            $table->index(['interview_stage_id', 'status']);
            $table->index(['interviewer_id', 'created_at']);
        });

        // 5. Rincian Skor per Poin Penilaian
        Schema::create('interview_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('interview_session_id')->constrained('interview_sessions')->cascadeOnDelete();
            $table->foreignId('interview_criterion_id')->constrained('interview_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['interview_session_id', 'interview_criterion_id'], 'interview_scores_session_criterion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_scores');
        Schema::dropIfExists('interview_sessions');
        Schema::dropIfExists('interview_criteria');
        Schema::dropIfExists('interview_stages');
        Schema::dropIfExists('interview_batches');
    }
};

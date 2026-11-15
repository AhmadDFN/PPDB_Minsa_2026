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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            // Official registration number assigned upon final submission, nullable during draft
            $table->string('registration_number', 50)->nullable()->unique();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('admission_period_id')->constrained('admission_periods')->restrictOnDelete();
            $table->foreignId('admission_period_program_id')->constrained('admission_period_programs')->restrictOnDelete();

            // Status strictly limited to DRAFT or SUBMITTED
            $table->string('status', 20)->default('DRAFT');
            $table->dateTime('submitted_at')->nullable();

            // Incremented on every meaningful post-submission data revision
            $table->unsignedInteger('data_revision')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Critical business rule: One applicant can only register once per admission period
            $table->unique(['applicant_id', 'admission_period_id']);

            $table->index('status');
            $table->index(['admission_period_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};

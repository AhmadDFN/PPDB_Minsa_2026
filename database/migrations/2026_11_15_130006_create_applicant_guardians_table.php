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
        Schema::create('applicant_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            // Relationship type: FATHER, MOTHER, GUARDIAN
            $table->string('relationship_type', 20);
            $table->string('name');
            $table->string('nik', 16)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('education', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('monthly_income', 50)->nullable();
            $table->string('phone', 25)->nullable();
            $table->boolean('is_alive')->default(true);
            $table->timestamps();

            // Crucial business rule: An applicant cannot have duplicate relationship records
            // (e.g. exactly one father, one mother, zero or one guardian)
            $table->unique(['applicant_id', 'relationship_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_guardians');
    }
};

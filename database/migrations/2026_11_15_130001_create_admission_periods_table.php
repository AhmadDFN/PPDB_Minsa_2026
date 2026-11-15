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
        Schema::create('admission_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year', 15);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            // Age reference date: date from which applicant age (years, months, days) is calculated
            $table->date('age_reference_date');
            $table->string('status', 20)->default('draft');
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_periods');
    }
};

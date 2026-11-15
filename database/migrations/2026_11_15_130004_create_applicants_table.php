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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            // One user has exactly one applicant profile
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('gender', 5); // 'L' (Laki-laki) or 'P' (Perempuan)
            $table->string('birth_place');
            $table->date('birth_date');
            $table->string('nisn', 10)->nullable();
            $table->unsignedSmallInteger('child_order')->nullable();
            $table->unsignedSmallInteger('siblings_count')->nullable();
            $table->timestamps();

            $table->index('birth_date');
            $table->index('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};

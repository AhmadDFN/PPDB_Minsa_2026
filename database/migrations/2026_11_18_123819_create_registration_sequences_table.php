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
        Schema::create('registration_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_period_program_id')
                ->unique()
                ->constrained('admission_period_programs')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_sequences');
    }
};

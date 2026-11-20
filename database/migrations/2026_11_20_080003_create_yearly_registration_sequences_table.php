<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for 5-digit YYNNN registration number sequence per admission intake year.
     */
    public function up(): void
    {
        Schema::create('yearly_registration_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('intake_year', 4)->unique(); // e.g. '27' for 2027
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearly_registration_sequences');
    }
};

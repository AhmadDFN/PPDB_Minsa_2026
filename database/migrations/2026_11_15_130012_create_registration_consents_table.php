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
        Schema::create('registration_consents', function (Blueprint $table) {
            $table->id();
            // One consent per registration record
            $table->foreignId('registration_id')->unique()->constrained('registrations')->cascadeOnDelete();
            $table->text('statement_text');
            $table->boolean('is_agreed')->default(true);
            $table->dateTime('agreed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_consents');
    }
};

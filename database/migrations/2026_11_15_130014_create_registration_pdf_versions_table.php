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
        Schema::create('registration_pdf_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            // 'draft' or 'final'
            $table->string('document_type', 20);
            $table->unsignedInteger('version_number')->default(1);
            // Links PDF directly to data revision counter to detect outdated PDFs
            $table->unsignedInteger('data_revision');
            $table->string('file_path');
            $table->string('file_hash', 64);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('generated_at');
            $table->timestamps();

            // Version number must be unique per registration and document type
            $table->unique(['registration_id', 'document_type', 'version_number'], 'reg_pdf_version_unique');
            $table->index(['registration_id', 'data_revision']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_pdf_versions');
    }
};

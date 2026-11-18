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
        if (! Schema::hasTable('registration_documents')) {
            Schema::create('registration_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
                $table->foreignId('document_requirement_id')->constrained('document_requirements')->restrictOnDelete();
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('file_size_bytes');
                $table->string('file_hash', 64);
                $table->dateTime('uploaded_at');
                $table->timestamps();

                $table->unique(
                    ['registration_id', 'document_requirement_id'],
                    'registration_docs_reg_requirement_unique',
                );
            });

            return;
        }

        if (
            ! Schema::hasIndex('registration_documents', 'registration_docs_reg_requirement_unique')
            && ! Schema::hasIndex('registration_documents', ['registration_id', 'document_requirement_id'])
        ) {
            Schema::table('registration_documents', function (Blueprint $table) {
                $table->unique(
                    ['registration_id', 'document_requirement_id'],
                    'registration_docs_reg_requirement_unique',
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_documents');
    }
};

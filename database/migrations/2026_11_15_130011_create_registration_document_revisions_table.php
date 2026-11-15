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
        Schema::create('registration_document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_document_id')->constrained('registration_documents')->cascadeOnDelete();
            $table->unsignedInteger('revision_number')->default(1);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('file_hash', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('replaced_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['registration_document_id', 'revision_number'], 'reg_doc_rev_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_document_revisions');
    }
};

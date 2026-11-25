<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_documents', function (Blueprint $table): void {
            $table->string('verification_status', 30)->default('PENDING')->after('uploaded_at');
            $table->string('verification_reason')->nullable()->after('verification_status');
            $table->foreignId('verified_by')->nullable()->after('verification_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable()->after('verified_by');
            $table->index(['verification_status', 'registration_id'], 'registration_docs_verification_idx');
        });

        Schema::create('registration_document_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_document_id')
                ->constrained('registration_documents', indexName: 'reg_doc_verifications_doc_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['registration_document_id', 'created_at'], 'registration_doc_verification_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_document_verifications');
        Schema::table('registration_documents', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropIndex('registration_docs_verification_idx');
            $table->dropColumn(['verification_status', 'verification_reason', 'verified_by', 'verified_at']);
        });
    }
};

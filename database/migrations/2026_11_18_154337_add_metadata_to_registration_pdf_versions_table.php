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
        Schema::table('registration_pdf_versions', function (Blueprint $table): void {
            $table->string('storage_disk')->default('ppdb_private');
            $table->string('mime_type', 127)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('verification_token_hash', 64)->nullable();

            $table->unique('verification_token_hash', 'reg_pdf_verification_token_hash_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_pdf_versions', function (Blueprint $table): void {
            $table->dropUnique('reg_pdf_verification_token_hash_unique');
            $table->dropColumn([
                'storage_disk',
                'mime_type',
                'file_size_bytes',
                'verification_token_hash',
            ]);
        });
    }
};

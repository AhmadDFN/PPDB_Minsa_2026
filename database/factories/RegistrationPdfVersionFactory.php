<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PdfDocumentType;
use App\Models\Registration;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationPdfVersion>
 */
class RegistrationPdfVersionFactory extends Factory
{
    protected $model = RegistrationPdfVersion::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'document_type' => PdfDocumentType::FINAL,
            'version_number' => 1,
            'data_revision' => 1,
            'file_path' => 'ppdb/pdfs/'.fake()->uuid().'.pdf',
            'file_hash' => hash('sha256', fake()->text()),
            'storage_disk' => 'ppdb_private',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'verification_token_hash' => null,
            'generated_by' => User::factory(),
            'generated_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'document_type' => PdfDocumentType::DRAFT,
        ]);
    }

    public function final(): static
    {
        return $this->state(fn () => [
            'document_type' => PdfDocumentType::FINAL,
        ]);
    }
}

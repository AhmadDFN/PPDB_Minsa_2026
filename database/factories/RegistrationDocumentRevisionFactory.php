<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RegistrationDocument;
use App\Models\RegistrationDocumentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationDocumentRevision>
 */
class RegistrationDocumentRevisionFactory extends Factory
{
    protected $model = RegistrationDocumentRevision::class;

    public function definition(): array
    {
        return [
            'registration_document_id' => RegistrationDocument::factory(),
            'revision_number' => 1,
            'file_path' => 'private/ppdb/documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => fake()->numberBetween(50000, 1500000),
            'file_hash' => hash('sha256', fake()->text()),
            'uploaded_by' => User::factory(),
            'replaced_at' => null,
            'reason' => null,
        ];
    }
}

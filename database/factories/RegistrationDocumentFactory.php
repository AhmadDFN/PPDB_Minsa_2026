<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentRequirement;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationDocument>
 */
class RegistrationDocumentFactory extends Factory
{
    protected $model = RegistrationDocument::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'document_requirement_id' => DocumentRequirement::factory(),
            'file_path' => 'private/ppdb/documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => fake()->numberBetween(50000, 1500000),
            'file_hash' => hash('sha256', fake()->text()),
            'uploaded_at' => now(),
        ];
    }
}
